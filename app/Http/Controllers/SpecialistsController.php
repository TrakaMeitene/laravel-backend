<?php

namespace App\Http\Controllers;

use Carbon\CarbonImmutable;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonInterval;

class SpecialistsController extends Controller
{
    public function getspecialists(Request $request)
    {
        $specialists = User::where('scope', 'business')
            ->where('city', $request->city)
            ->when(!empty($request->occupation), function ($query) use ($request) {
                $query->where('occupation', $request->occupation);
            })
            ->get();

        foreach ($specialists as $key => $value) {

            $isAbonent = $value->abonament;

            if ($isAbonent === "bezmaksas") {
                $allbookings = $value->bookings;
                $count = $allbookings->whereBetween("created_at", [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->count();
                if ($count >= 20) {
                    $specialists->forget($key);
                }
            }
        }
        return $specialists;
    }

    public function getspecialistbyname(Request $request)
    {
        $name = $request->name;
        $specialistbyname = User::where(['urlname' => $name])->get();

        return $specialistbyname;
    }

    public function getspecialistbyid(Request $request)
    {
        $id = $request['id'];
        $specialistbyid = User::where('id', $id)->get();

        return $specialistbyid;
    }

    public function getSpecialistsTimes(Request $request)
    {
        $user = User::where('id', $request->userid)->first();
        $range = $request->range;
        $serviceduration = $user->services->find($request->service)->time;
        $hasBookings = $user->activeBookings->whereBetween('date', [$range[0], Carbon::parse(last($range))->setTimezone('Europe/Riga')->addHours(23)->addMinutes(59)])->flatten();

        $times = collect();
        $bookings = $hasBookings->map(function ($booking, $key) {
            return CarbonInterval::minutes(60)->toPeriod(Carbon::parse($booking->date)->setTimezone('Europe/Riga'), Carbon::parse($booking->end)->setTimezone('Europe/Riga'));
        });

        $isAbonent = $user->abonament;

        if ($isAbonent === "bezmaksas") {
            $allbookings = $user->bookings;
            $count = $allbookings->whereBetween("created_at", [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->count();
            if ($count >= 20) {
                return ["Pieraksts aizvērts!"];
            }
        }


        foreach ($range as $key => $date) {
            $daySettings = $user->settings->where('day', Carbon::parse($date)->setTimezone('Europe/Riga')->dayOfWeekIso)->flatten();


            $isDayVacation = $user->vacation->where('date', Carbon::parse($date)->setTimezone('Europe/Riga')->format('Y-m-d'))->flatten();
            $startHour = $daySettings->count() == 0 ? "8.00" : explode(':', $daySettings[0]->from);
            $endTimechunks = $daySettings->count() == 0 ? "17.00" : explode(':', $daySettings[0]->to);
            $breakFrom = $daySettings->count() == 0 ? "12.00" : explode(':', $daySettings[0]->breakfrom);
            $breakTo = $daySettings->count() == 0 ? "13.00" : explode(':', $daySettings[0]->breakto);
            $breakstartdate = Carbon::parse($date)->setTimezone('Europe/Riga')->addHours((int) $breakFrom[0])->addMinutes((int) $breakFrom[1]);
            $breakenddate = Carbon::parse($date)->setTimezone('Europe/Riga')->addHours((int) $breakTo[0])->addMinutes((int) $breakTo[1]);
            $breakinterval = CarbonInterval::minutes(60)->toPeriod($breakstartdate, $breakenddate->addMinutes(-1))->toArray();
            $interval = CarbonInterval::minutes(60)->toPeriod(Carbon::parse($date)->setTimezone('Europe/Riga')->addHours((int) $startHour[0])->addMinutes((int) $startHour[1]), Carbon::parse($date)->setTimezone('Europe/Riga')->addHours((int) $endTimechunks[0])->addMinutes((int) $endTimechunks[1]))->toArray();
            $timesWithoutBreak = array_values(array_diff($interval, $breakinterval));

            $bookingsRemoved = $bookings->count() != 0 && $bookings->map(function ($booking) use ($date, $timesWithoutBreak) {
                if (($booking->toArray())[0]->format('y-m-d') === Carbon::parse($date)->setTimezone('Europe/Riga')->format('y-m-d')) {
                    return $booking->toArray();
                }
            });


            $timesWhitoutBookings = collect($timesWithoutBreak)->filter(function ($date) use ($bookingsRemoved) {
                return !in_array($date, $bookingsRemoved->filter()->flatten()->toArray());
            });

            $timesThatOverlapWorkingtime = $timesWhitoutBookings->map(function ($item, $key) use ($date, $serviceduration, $endTimechunks) {
                if (CarbonImmutable::parse($date)->addHours((int) $endTimechunks[0])->addMinutes((int) $endTimechunks[1])->lessThan(CarbonImmutable::parse($item)->addMinutes($serviceduration))) {
                    return $item;
                }
            });

            $timesWithinWorkingTime = $timesWhitoutBookings->diff($timesThatOverlapWorkingtime)->flatten();

            $serviceNeededTimes = $timesWhitoutBookings->map(function ($time, $key) use ($serviceduration) {
                return CarbonInterval::minutes(60)->toPeriod($time, CarbonImmutable::parse($time)->addMinutes($serviceduration - 1))->setTimezone('Europe/Riga');
            });

            $particulartimechucksToremove = $serviceNeededTimes->map(function ($time, $key) use ($timesWhitoutBookings) {
                if (count(array_intersect($time->toArray(), $timesWhitoutBookings->toArray())) !== count($time->toArray())) {
                    return $time;
                };
            });

            $timesFromChunksToRemove = $particulartimechucksToremove->map(function ($time, $key) use ($serviceNeededTimes) {
                return $time ? $time->toArray()[0] : null;
            });

            $timestoreturn = $timesWithinWorkingTime->diff($timesFromChunksToRemove);

            $vacations = $isDayVacation->map(function ($data, $key) {
                return Carbon::parse($data->date)->setTimezone('Europe/Riga')->format('y-m-d');
            });


            //te izņem brīvdienas/vacations
            $responseTimes = collect($timestoreturn)->map(function ($date) use ($vacations) {
                if (!$vacations->contains(Carbon::parse($date)->format('y-m-d'))) {
                    return $date;
                };
            });



            $times->push([
                'date' => $date,
                'isDayFree' => $daySettings[0]->statuss === 1 ? true : false,
                'isDayVacation' => $isDayVacation->count() === 1 ? true : false,
                'start' => Carbon::parse($date)->setTimezone('Europe/Riga')->addHours((int) $startHour[0])->addMinutes((int) $startHour[1]),
                'end' => Carbon::parse($date)->setTimezone('Europe/Riga')->addHours((int) $endTimechunks[0])->addMinutes((int) $endTimechunks[1]),
                'interval' => $responseTimes->flatten()->filter()

            ]);
        }


        return $times;

    }

    public function getspecialistapi()
    {
        info("te atnaca");
        return "teksts";
    }
}
