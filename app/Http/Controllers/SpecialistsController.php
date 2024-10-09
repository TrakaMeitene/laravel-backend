<?php

namespace App\Http\Controllers;

use DB;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Vacation;
use Carbon\CarbonInterval;

class SpecialistsController extends Controller
{
    public function getspecialists(Request $request)
    {
        $specialists = User::where(['scope' => 'business', 'city' => $request->city])->get();
        return $specialists;
    }

    public function getspecialistbyname(Request $request)
    {
        $name = $request->name;
        $specialistbyname = User::where(['urlname' => $name])->get();

        return $specialistbyname;
    }

    public function getSpecialistsTimes(Request $request)
    {
        $user = User::where('id', $request->userid)->first();
        $range = $request->range;
        $serviceduration = $user->services->find($request->service)->time;
        $hasBookings = $user->bookings->whereBetween('date', [$range[0], Carbon::parse($range[7])->setTimezone('Europe/Riga')->addHours(23)->addMinutes(59)])->flatten();
        $times = collect();
        $bookings = $hasBookings->map(function ($booking, $key) {
            return CarbonInterval::minutes(60)->toPeriod(Carbon::parse($booking->date)->setTimezone('Europe/Riga'), Carbon::parse($booking->end)->setTimezone('Europe/Riga'));
        });


        foreach ($range as $key => $date) {
            $daySettings = $user->settings->where('day', Carbon::parse($date)->setTimezone('Europe/Riga')->dayOfWeekIso)->flatten();
            $isDayVacation = $user->vacation->where('date', Carbon::parse($date)->setTimezone('Europe/Riga')->format('Y-m-d'))->flatten();
            $startHour = explode(':', $daySettings[0]->from);
            $endTimechunks = explode(':', $daySettings[0]->to);
            $breakFrom = explode(':', $daySettings[0]->breakfrom);
            $breakTo = explode(':', $daySettings[0]->breakto);
            $breakstartdate = Carbon::parse($date)->setTimezone('Europe/Riga')->addHours((int) $breakFrom[0])->addMinutes((int) $breakFrom[1]);
            $breakenddate = Carbon::parse($date)->setTimezone('Europe/Riga')->addHours((int) $breakTo[0])->addMinutes((int) $breakTo[1]);
            $breakinterval = CarbonInterval::minutes(60)->toPeriod($breakstartdate, $breakenddate)->toArray();
            $interval = CarbonInterval::minutes(60)->toPeriod(Carbon::parse($date)->setTimezone('Europe/Riga')->addHours((int) $startHour[0])->addMinutes((int) $startHour[1]), Carbon::parse($date)->setTimezone('Europe/Riga')->addHours((int) $endTimechunks[0] - 1)->addMinutes((int) $endTimechunks[1] + 59))->toArray();

            $timesWithoutBreak = array_values(array_diff($interval, $breakinterval));

            $bookingsRemoved = $bookings->map(function ($booking) use ($date, $timesWithoutBreak) {
                if (($booking->toArray())[0]->format('y-m-d') == Carbon::parse($date)->setTimezone('Europe/Riga')->format('y-m-d')) {
                    return array_diff($timesWithoutBreak, $booking->toArray());
                }
            })->flatten();
            $timesWhitoutBookings = $bookingsRemoved->flatten()->filter()->count() == 0 ? $timesWithoutBreak : $bookingsRemoved->filter()->flatten();


            $serviceneededtimes = collect($timesWhitoutBookings)->map(function ($time, $key) use ($serviceduration) {
                return CarbonInterval::minutes(60)->toPeriod($time, Carbon::parse($time)->addMinutes($serviceduration))->setTimezone('Europe/Riga');
            });


            $avialabletimes = $serviceneededtimes->map(function ($time, $key) use ($date, $serviceneededtimes, $timesWhitoutBookings) {
                if (count(array_intersect(collect($timesWhitoutBookings)->all(), $time->toArray())) !== count($time->toArray())) {
                    return array_diff(collect($timesWhitoutBookings)->all(), [($time->toArray())[0]]);

                } else {
                    return $timesWhitoutBookings;
                }
            });


            $ff = collect($avialabletimes[0])->map(function ($item, $key) use ($date, $serviceduration, $endTimechunks, $avialabletimes) {
                info(Carbon::parse($date)->setTimezone('Europe/Riga')->addHours((int) $endTimechunks[0])->addMinutes((int) $endTimechunks[1]). " - " . (Carbon::parse($item)->addMinutes($serviceduration)));
                if (Carbon::parse($date)->addHours((int) $endTimechunks[0])->addMinutes((int) $endTimechunks[1])->lessThanOrEqualTo(Carbon::parse($item)->addMinutes($serviceduration))) {
                    return collect($avialabletimes[0])->diff([$item]);
                }
            });

            info($ff);

            $times->push([
                'date' => $date,
                'isDayFree' => $daySettings[0]->statuss,
                'isDayVacation' => $isDayVacation->count() === 1 ? true : false,
                'start' => Carbon::parse($date)->setTimezone('Europe/Riga')->addHours((int) $startHour[0])->addMinutes((int) $startHour[1]),
                'end' => Carbon::parse($date)->setTimezone('Europe/Riga')->addHours((int) $endTimechunks[0])->addMinutes((int) $endTimechunks[1]),
                'interval' => array_values(collect($avialabletimes[0])->all()),
                'dd' => collect(($avialabletimes->toArray())[0])->flatten(),
                'interval21' => $ff
            ]);
        }


        return $times;

    }
}
