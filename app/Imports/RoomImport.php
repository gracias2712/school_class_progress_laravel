<?php

namespace App\Imports;

use App\Models\Room;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use UnexpectedValueException;

class RoomImport implements ToCollection
{
    private const ROOM_HEADERS = [
        'nama_ruangan',
        'nama_ruang',
        'ruangan',
        'ruang',
        'room_name',
        'room',
        'nama_laboratorium',
        'laboratorium',
        'lab_name',
    ];

    public function collection(Collection $rows): void
    {
        $rawRows = $rows->map(
            static fn ($row) => $row instanceof Collection ? $row->all() : (array) $row
        )->all();

        $mapping = $this->scanLegend($rawRows);
        $roomCodes = [];
        $hasRoomHeaders = false;
        $hasScheduleRows = false;
        $activeRoomColumns = [];

        foreach ($rawRows as $row) {
            $roomColumns = $this->roomHeaderColumns($row);
            if ($roomColumns !== []) {
                $hasRoomHeaders = true;
                $activeRoomColumns = $roomColumns;
                continue;
            }

            foreach ($activeRoomColumns as $column) {
                if (array_key_exists($column, $row)) {
                    $this->addCode($roomCodes, $row[$column], true);
                }
            }

            $timeColumns = $this->timeRangeColumns($row);
            if ($timeColumns === []) {
                continue;
            }

            $hasScheduleRows = true;
            $firstTimeColumn = min($timeColumns);
            foreach ($row as $column => $value) {
                if ($column > $firstTimeColumn) {
                    $this->addCode($roomCodes, $value);
                }
            }
        }

        if (!$hasScheduleRows && !$hasRoomHeaders) {
            foreach ($rawRows as $row) {
                foreach (array_slice($row, 0, 2, true) as $value) {
                    $this->addCode($roomCodes, $value);
                }
            }
        }

        foreach (array_keys($mapping) as $code) {
            $roomCodes[$code] = $code;
        }

        $roomNames = [];
        foreach ($roomCodes as $code) {
            $roomName = $mapping[$code] ?? $this->formatRoomName($code);
            $roomNames[Str::upper($roomName)] = $roomName;
        }

        foreach ($roomNames as $roomName) {
            if (Str::length($roomName) > 50) {
                throw new UnexpectedValueException(
                    "Nama ruangan '{$roomName}' melebihi batas 50 karakter."
                );
            }
        }

        foreach ($roomNames as $roomName) {
            Room::updateOrCreate(
                ['room_name' => Str::upper($roomName)],
                []
            );
        }
    }

    private function scanLegend(array $rawRows): array
    {
        $mapping = [];

        foreach ($rawRows as $row) {
            $parts = array_filter(
                array_map(
                    static fn ($value) => trim(preg_replace('/\s+/u', ' ', (string) $value)),
                    $row
                ),
                static fn ($value) => $value !== ''
            );
            $line = implode(' ', $parts);

            if (!preg_match(
                '/^\s*(L\.[A-Z0-9]+|LIPA|[A-Z]{1,8}\d*|\d{1,4})\s*[=:]\s*(.+?)\s*$/iu',
                $line,
                $matches
            )) {
                continue;
            }

            $code = $this->normalizeCode($matches[1]);
            $description = $this->normalizeDescription($matches[2]);

            if ($code !== null && $description !== null) {
                $mapping[$code] = $description;
            }
        }

        return $mapping;
    }

    private function roomHeaderColumns(array $row): array
    {
        $columns = [];

        foreach ($row as $column => $value) {
            $header = strtolower(trim(
                preg_replace('/[^a-z0-9]+/i', '_', (string) $value),
                '_'
            ));

            if (in_array($header, self::ROOM_HEADERS, true)) {
                $columns[] = $column;
            }
        }

        return $columns;
    }

    private function timeRangeColumns(array $row): array
    {
        $columns = [];

        foreach ($row as $column => $value) {
            if (preg_match('/^\s*\d{1,2}[.:]\d{2}\s*-\s*\d{1,2}[.:]\d{2}\s*$/', (string) $value)) {
                $columns[] = $column;
            }
        }

        return $columns;
    }

    private function addCode(array &$roomCodes, mixed $value, bool $allowDescriptiveName = false): void
    {
        $code = $this->normalizeCode($value);
        if ($code === null) {
            return;
        }

        if (!$allowDescriptiveName && !$this->isRoomCode($code)) {
            return;
        }

        $roomCodes[$code] = $code;
    }

    private function normalizeCode(mixed $value): ?string
    {
        $code = Str::upper(trim(preg_replace('/\s+/u', ' ', (string) $value)));
        $code = trim($code, " \t\n\r\0\x0B:;,-");

        if ($code === '' || in_array($code, [
            '-',
            '-X-',
            'HARI',
            'JAM',
            'WAKTU',
            'TIME',
            'ISTIRAHAT',
            'UPACARA',
            'LITERASI',
            'KETERANGAN',
            'NAMA',
            'NO',
            'NOMOR',
            'TOTAL',
        ], true)) {
            return null;
        }

        return $code;
    }

    private function isRoomCode(string $code): bool
    {
        return is_numeric($code) || preg_match(
            '/^(?:L\.[A-Z0-9]+|LIPA|LAB(?:ORATORIUM)?(?:\s+[A-Z0-9][A-Z0-9 .\/-]*)?|ROOM\s*[A-Z0-9][A-Z0-9 .\/-]*|RUANG(?:AN)?\s*[A-Z0-9][A-Z0-9 .\/-]*|RM[- ]?[A-Z0-9]+|HALL|OR[0-9]*|BENGKEL(?:\s+[A-Z0-9][A-Z0-9 .\/-]*)?|LAPANGAN(?:\s+[A-Z0-9][A-Z0-9 .\/-]*)?)$/u',
            $code
        ) === 1;
    }

    private function normalizeDescription(string $description): ?string
    {
        $description = Str::upper(trim(preg_replace('/\s+/u', ' ', $description)));
        $description = trim($description, " \t\n\r\0\x0B:;=,-");

        return $description === '' ? null : $description;
    }

    private function formatRoomName(string $code): string
    {
        return is_numeric($code) ? 'RUANG ' . $code : Str::upper($code);
    }
}
