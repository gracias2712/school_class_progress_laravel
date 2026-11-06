<?php

namespace App\Imports;

use App\Models\Classe;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\User;
use DateTimeImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use RuntimeException;

class ScheduleImport implements ToCollection, WithMultipleSheets
{
    private const DAY_HEADERS = [
        'SENIN',
        'SELASA',
        'RABU',
        'KAMIS',
        'JUMAT',
    ];

    private const DAYS = [
        'SENIN' => 'Senin',
        'SELASA' => 'Selasa',
        'RABU' => 'Rabu',
        'KAMIS' => 'Kamis',
        'JUMAT' => 'Jumat',
        'JUM’AT' => 'Jumat',
        "JUM'AT" => 'Jumat',
    ];

    public function sheets(): array
    {
        return [0 => $this];
    }

    public function collection(Collection $rows): void
    {
        $rows = $rows->values();
        $currentClassId = null;
        $currentClass = null;
        $activeCycle = trim((string) request()->post('cycle_type', '1'));
        $cycleNumber = preg_replace('/^Putaran\s*/i', '', $activeCycle);

        if (!in_array($cycleNumber, ['1', '2'], true)) {
            throw new InvalidArgumentException('Jenis putaran jadwal harus 1 atau 2.');
        }

        $cycleType = 'Putaran ' . $cycleNumber;
        $hariHeaders = array_fill_keys(self::DAY_HEADERS, -1);
        $classLookup = [];
        $teacherLookup = [];
        $rooms = Room::all(['id', 'room_name']);

        foreach ($rows as $rowIndex => $row) {
            $classChanged = false;

            foreach ($row as $cell) {
                $className = $this->normalizeText($cell);
                $normalizedClassName = Str::upper(preg_replace('/\s+/u', '', $className));

                if (
                    Str::length($className) < 3
                    || Str::length($className) > 15
                    || in_array($normalizedClassName, array_keys(self::DAYS), true)
                ) {
                    continue;
                }

                if (!array_key_exists($normalizedClassName, $classLookup)) {
                    $classLookup[$normalizedClassName] = Classe::whereRaw(
                        "REPLACE(UPPER(class_name), ' ', '') = ?",
                        [$normalizedClassName]
                    )->first();
                }

                if ($classLookup[$normalizedClassName]) {
                    $currentClass = $classLookup[$normalizedClassName];
                    $currentClassId = $currentClass->id;
                    $hariHeaders = array_fill_keys(self::DAY_HEADERS, -1);
                    $classChanged = true;
                    break;
                }
            }

            if ($classChanged || !$currentClassId) {
                continue;
            }

            $isHeaderRow = false;
            foreach ($row as $colIdx => $cell) {
                $dayHeader = $this->normalizeDayHeader($cell);
                if ($dayHeader !== null && array_key_exists($dayHeader, $hariHeaders)) {
                    $hariHeaders[$dayHeader] = $colIdx;
                    $isHeaderRow = true;
                }
            }

            if ($isHeaderRow || in_array(-1, $hariHeaders, true)) {
                continue;
            }

            [$jamMulai, $timeColumn, $explicitEndTime] = $this->findTimesInRow($row);
            if ($jamMulai === null || $timeColumn === null) {
                continue;
            }

            $jamSelesai = $explicitEndTime;
            if (
                $jamSelesai === null
                || $this->minutesBetween($jamMulai, $jamSelesai) <= 0
            ) {
                $jamSelesai = $this->addMinutes($jamMulai, 45);
                $nextTime = $this->findNextTimeInColumn($rows, $rowIndex, $timeColumn);

                if ($nextTime !== null && $this->minutesBetween($jamMulai, $nextTime) === 45) {
                    $jamSelesai = $nextTime;
                }
            }

            foreach ($hariHeaders as $namaHari => $colIdx) {
                if (!isset($row[$colIdx])) {
                    continue;
                }

                $isiKotak = $this->normalizeText($row[$colIdx]);
                if (
                    $isiKotak === ''
                    || strtoupper($isiKotak) === '-X-'
                    || str_contains(strtoupper($isiKotak), 'ISTIRAHAT')
                ) {
                    continue;
                }

                $pattern = '/(.*?)\s*"(\d+)\.\s*(.*?)(?:"|\s*$)\s*(.*)/is';
                if (!preg_match($pattern, $isiKotak, $matches)) {
                    continue;
                }

                $mapelTeks = $this->normalizeText($matches[1]);
                $namaGuruRaw = $this->normalizeText($matches[3]);
                $ruangTeks = $this->normalizeText($matches[4]);

                if ($ruangTeks === '') {
                    $ruangTeks = $this->extractTrailingRoom($namaGuruRaw);
                    if ($ruangTeks !== '') {
                        $namaGuruRaw = trim(
                            preg_replace('/\s+' . preg_quote($ruangTeks, '/') . '\s*$/i', '', $namaGuruRaw)
                        );
                    }
                }

                $guruObj = $this->findTeacher($namaGuruRaw, $teacherLookup);
                if (!$guruObj) {
                    continue;
                }

                $roomId = $this->findRoomId($ruangTeks, $currentClass, $rooms);
                if (!$roomId) {
                    throw new RuntimeException(
                        "Tidak ada ruangan yang dapat digunakan untuk jadwal kelas {$currentClass->class_name}."
                    );
                }

                $schedule = [
                    'teacher_id' => $guruObj->id,
                    'class_id' => $currentClassId,
                    'room_id' => $roomId,
                    'subject' => $mapelTeks ?: ($guruObj->subject ?: 'UMUM'),
                    'day_of_week' => self::DAYS[$namaHari],
                    'start_time' => $jamMulai,
                    'end_time' => $jamSelesai,
                    'cycle_type' => $cycleType,
                ];

                $this->persistSchedule($schedule, $cycleNumber);
            }
        }
    }

    private function findTimesInRow(iterable $row): array
    {
        $times = [];

        foreach ($row as $column => $cell) {
            $value = trim((string) $cell);
            if (preg_match('/^([0-9]{2}:[0-9]{2})$/', $value, $matches)) {
                $times[] = [
                    'column' => $column,
                    'time' => $matches[1],
                ];
            }
        }

        if ($times === []) {
            return [null, null, null];
        }

        return [
            $times[0]['time'],
            $times[0]['column'],
            $times[1]['time'] ?? null,
        ];
    }

    private function findNextTimeInColumn(Collection $rows, int $currentRowIndex, int $timeColumn): ?string
    {
        foreach ($rows->slice($currentRowIndex + 1) as $row) {
            if (!isset($row[$timeColumn])) {
                continue;
            }

            $value = trim((string) $row[$timeColumn]);
            if (preg_match('/^([0-9]{2}:[0-9]{2})$/', $value, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    private function persistSchedule(array $schedule, string $cycleNumber): void
    {
        $conflict = Schedule::where(function ($query) use ($cycleNumber) {
            $query->where('cycle_type', $cycleNumber)
                ->orWhere('cycle_type', 'Putaran ' . $cycleNumber);
        })
            ->where('day_of_week', $schedule['day_of_week'])
            ->where(function ($query) use ($schedule) {
                $query->where('room_id', $schedule['room_id'])
                    ->orWhere('class_id', $schedule['class_id'])
                    ->orWhere('teacher_id', $schedule['teacher_id']);
            })
            ->where('start_time', '<', $schedule['end_time'])
            ->where('end_time', '>', $schedule['start_time'])
            ->exists();

        if ($conflict) {
            return;
        }

        Schedule::firstOrCreate([
            'teacher_id' => $schedule['teacher_id'],
            'class_id' => $schedule['class_id'],
            'room_id' => $schedule['room_id'],
            'day_of_week' => $schedule['day_of_week'],
            'start_time' => $schedule['start_time'],
            'end_time' => $schedule['end_time'],
            'cycle_type' => $schedule['cycle_type'],
        ], [
            'subject' => $schedule['subject'],
        ]);
    }

    private function normalizeText(mixed $value): string
    {
        $value = str_replace(["\u{00A0}", "\u{202F}"], ' ', (string) $value);
        $value = preg_replace('/[\r\n]+/u', ' ', $value);

        return trim(preg_replace('/\s+/u', ' ', $value));
    }

    private function normalizeDayHeader(mixed $value): ?string
    {
        $header = Str::upper($this->normalizeText($value));
        $header = str_replace(["\u{200B}", "\u{FEFF}"], '', $header);
        $header = preg_replace('/[\s\'"’‘`]+/u', '', $header);

        foreach (self::DAYS as $alias => $dayName) {
            $normalizedAlias = Str::upper(preg_replace('/[\s\'"’‘`]+/u', '', $alias));
            if ($header === $normalizedAlias) {
                return Str::upper($dayName);
            }
        }

        return null;
    }

    private function addMinutes(string $time, int $minutes): string
    {
        $start = DateTimeImmutable::createFromFormat('!H:i', $time);
        if (!$start) {
            throw new InvalidArgumentException("Format jam tidak valid: {$time}");
        }

        return $start->modify("+{$minutes} minutes")->format('H:i');
    }

    private function minutesBetween(string $start, string $end): int
    {
        $startTime = DateTimeImmutable::createFromFormat('!H:i', $start);
        $endTime = DateTimeImmutable::createFromFormat('!H:i', $end);

        if (!$startTime || !$endTime) {
            throw new InvalidArgumentException('Format jam mulai atau selesai tidak valid.');
        }

        return (int) (($endTime->getTimestamp() - $startTime->getTimestamp()) / 60);
    }

    private function findTeacher(string $rawName, array &$teacherLookup): ?User
    {
        $rawName = $this->normalizeText(str_replace('/', ' ', $rawName));
        $nameWithoutTitle = trim(explode(',', $rawName)[0]);
        $cacheKey = Str::upper($nameWithoutTitle);

        if ($cacheKey === '') {
            return null;
        }

        if (!array_key_exists($cacheKey, $teacherLookup)) {
            $teacher = User::whereIn('role_id', [2, 3])
                ->where('fullname', 'like', '%' . $nameWithoutTitle . '%')
                ->first();

            if (!$teacher) {
                $firstName = Str::of($nameWithoutTitle)->explode(' ')->first();
                if (Str::length($firstName) >= 3) {
                    $teacher = User::whereIn('role_id', [2, 3])
                        ->where('fullname', 'like', '%' . $firstName . '%')
                        ->first();
                }
            }

            $teacherLookup[$cacheKey] = $teacher;
        }

        return $teacherLookup[$cacheKey];
    }

    private function extractTrailingRoom(string $text): string
    {
        if (preg_match(
            '/\b(LAB(?:ORATORIUM)?\s*[A-Z0-9.-]+|OR\d*|HALL|BENGKEL(?:\s+[A-Z0-9.-]+)?)\s*$/i',
            $text,
            $matches
        )) {
            return trim($matches[1]);
        }

        return '';
    }

    private function findRoomId(string $roomText, Classe $classe, Collection $rooms): ?int
    {
        $roomText = Str::upper($this->normalizeText($roomText));

        if ($roomText !== '') {
            $isNumericCode = is_numeric($roomText);
            $exactRoomName = $isNumericCode ? 'RUANG ' . $roomText : $roomText;

            foreach ($rooms as $room) {
                $roomName = Str::upper($this->normalizeText($room->room_name));
                if ($roomName === $exactRoomName) {
                    return (int) $room->id;
                }
            }

            if (preg_match('/^(?:LAB(?:ORATORIUM)?|L)\.?\s*(\d+)$/', $roomText, $matches)) {
                $roomNumber = $matches[1];

                foreach ($rooms as $room) {
                    $roomName = Str::upper($this->normalizeText($room->room_name));
                    if (
                        str_contains($roomName, 'LAB')
                        && preg_match('/(?:^|\D)' . preg_quote($roomNumber, '/') . '$/', $roomName)
                    ) {
                        return (int) $room->id;
                    }
                }
            }

            if (!$isNumericCode) {
                foreach ($rooms as $room) {
                    $roomName = Str::upper($this->normalizeText($room->room_name));
                    if (str_contains($roomName, $roomText)) {
                        return (int) $room->id;
                    }
                }
            }
        }

        $classRoomName = Str::upper($this->normalizeText($classe->class_name));
        foreach ($rooms as $room) {
            if (Str::upper($this->normalizeText($room->room_name)) === $classRoomName) {
                return (int) $room->id;
            }
        }

        $firstRoom = $rooms->first();

        return $firstRoom ? (int) $firstRoom->id : null;
    }
}
