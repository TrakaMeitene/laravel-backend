<?php

namespace App\Http\Controllers;

use Carbon\CarbonImmutable;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Carbon\CarbonPeriod;


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

            if ($isAbonent === "bezmaksas" && !$value->subscription('prod_ROmEFILN29hPqt')) {
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
        $hasBookings = $user->activeBookings->whereBetween('date', [$range[0], Carbon::parse(time: last($range))->setTimezone('Europe/Riga')->addHours(23)->addMinutes(59)])->flatten();
        $hasSpecilatimes = $user->specialtimes->where('service', $request->service);
        $times = collect();
        $bookings = $hasBookings->map(function ($booking, $key) use ($serviceduration) {
            return CarbonInterval::minutes(60)->toPeriod(Carbon::parse($booking->date)->setTimezone('Europe/Riga'), Carbon::parse($booking->end)->setTimezone('Europe/Riga'));
        });

        $isAbonent = $user->abonament;

        if ($isAbonent === "bezmaksas" && !$user->subscription('prod_ROmEFILN29hPqt')) {
            $allbookings = $user->bookings;
            $count = $allbookings->whereBetween("created_at", [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->count();
            if ($count >= 20) {
                return ["Pieraksts aizvērts!"];
            }
        }



        //jaizņem tie laiki, kas jau ir rezervācijās
//nerāda pareizi pa dienām
        if ($hasSpecilatimes->count() > 0) {
            foreach ($hasSpecilatimes as $key => $day) {

                $startHour = explode(":", $day->from);
                $endTimechunks = explode(":", $day->to);

                $interval = CarbonInterval::minutes($serviceduration)->toPeriod(Carbon::parse(Carbon::now())->setTimezone('Europe/Riga')->addHours((int) $startHour[0])->addMinutes((int) $startHour[1]), Carbon::now()->setTimezone('Europe/Riga')->addHours((int) $endTimechunks[0])->addMinutes((int) $endTimechunks[1]))->toArray();
                foreach ($range as $key => $date) {

                    $weekday = Carbon::parse($date)->setTimezone('Europe/Riga')->dayOfWeekIso;
                    $daySettings = $user->settings->where('day', Carbon::parse($date)->setTimezone('Europe/Riga')->dayOfWeekIso)->flatten();

                    $isDayVacation = $user->vacation->where('date', Carbon::parse($date)->setTimezone('Europe/Riga')->format('Y-m-d'))->flatten();

                    if (collect(json_decode($day->days))->flatten()->contains($weekday)) {
                        $startHour = explode(":", $day->from);
                        $endTimechunks = explode(":", $day->to);

                        $interval = CarbonInterval::minutes($serviceduration)->toPeriod(Carbon::parse($date)->setTimezone('Europe/Riga')->addHours((int) $startHour[0])->addMinutes((int) $startHour[1]), Carbon::parse($date)->setTimezone('Europe/Riga')->addHours((int) $endTimechunks[0])->addMinutes((int) $endTimechunks[1]))->toArray();

                        $bookingstoremove = $bookings->map(function ($booking) use ($date) {
                            if (($booking->toArray())[0]->format('y-m-d') === Carbon::parse($date)->setTimezone('Europe/Riga')->format('y-m-d')) {
                                return $booking->toArray();
                            }
                        });



                        $timesThatOverlapWorkingtime = collect($interval)->map(function ($item, $key) use ($date, $serviceduration, $endTimechunks) {
                            if (CarbonImmutable::parse($date)->addHours((int) $endTimechunks[0])->addMinutes((int) $endTimechunks[1])->lessThan(CarbonImmutable::parse($item)->addMinutes($serviceduration))) {
                                return $item;
                            }
                        });

                        $timesWithinWorkingTime = collect($interval)->diff($timesThatOverlapWorkingtime)->flatten();


                        $timesWhitoutBookings = collect($timesWithinWorkingTime)->filter(function ($date) use ($bookingstoremove) {
                            return !in_array($date, $bookingstoremove->filter()->flatten()->toArray());
                        });

                        $serviceNeededTimes = $timesWhitoutBookings->map(function ($time, $key) use ($serviceduration) {
                            return CarbonInterval::minutes($serviceduration)->toPeriod($time, CarbonImmutable::parse($time)->addMinutes($serviceduration - 1))->setTimezone('Europe/Riga');
                        });

                        $bookingEntTimestoremove = $hasBookings->map(function ($booking, $key) use ($timesWhitoutBookings) {
                            $set = collect();

                            collect($timesWhitoutBookings)->flatten()->unique()->map(function ($item) use ($booking, $set) {
                                if ($item->between($booking->date, $booking->end)) {
                                    return $set->push($item);
                                };
                            });
                            return $set->flatten()->filter();
                        });

                        $timestoreturn = (collect($timesWhitoutBookings)->flatten()->unique()->diff($bookingEntTimestoremove->flatten()));

                        $times->push([
                            'date' => $date,
                            'isDayFree' => $daySettings->count() !== 0 ? $daySettings[0]->statuss === 1 ? true : false : true,
                            'isDayVacation' => $isDayVacation->count() === 1 ? true : false,
                            'interval' => $timestoreturn,
                        ]);

                        $result = [];


                        foreach ($times as $item) {
                            $date = $item['date'];

                            if (!isset($result[$date])) {
                                $result[$date] = [
                                    "date" => $date,
                                    "isDayFree" => $item["isDayFree"],
                                    "isDayVacation" => $item["isDayVacation"],
                                    "interval" => [],
                                ];
                            }

                            // Apvienojam intervālus un nodrošinām, ka tie ir unikāli
                            $result[$date]['interval'] = collect($result[$date]['interval'])->merge(array($item['interval']));
                            $result[$date]['interval'] = $result[$date]['interval']->flatten();
                        }

                    }
                    ;

                }
            }
            $result = array(collect($result)->filter())[0]->sortBy('date');
            return array_values($result->toArray());
        }

        //īs ir parastajiem laikiem
        foreach ($range as $key => $date) {
            $daySettings = $user->settings->where('day', Carbon::parse($date)->setTimezone('Europe/Riga')->dayOfWeekIso)->flatten();


            $isDayVacation = $user->vacation->where('date', Carbon::parse($date)->setTimezone('Europe/Riga')->format('Y-m-d'))->flatten();
            $startHour = $daySettings->count() == 0 ? explode(':', "8:00") : explode(':', $daySettings[0]->from, 2);
            $endTimechunks = $daySettings->count() == 0 ? explode(':', "17:00") : explode(':', $daySettings[0]->to, 2);
            $breakFrom = $daySettings->count() == 0 ? explode(':', "12:00") : explode(':', $daySettings[0]->breakfrom);
            $breakTo = $daySettings->count() == 0 ? explode(":", "13:00") : explode(':', $daySettings[0]->breakto);
            $breakstartdate = Carbon::parse($date)->setTimezone('Europe/Riga')->addHours((int) $breakFrom[0])->addMinutes((int) $breakFrom[1]);
            $breakenddate = Carbon::parse($date)->setTimezone('Europe/Riga')->addHours((int) $breakTo[0])->addMinutes((int) $breakTo[1]);
            $breakinterval = CarbonInterval::minutes(60)->toPeriod($breakstartdate, $breakenddate->addMinutes(-1))->toArray();
            $interval = CarbonInterval::minutes(60)->toPeriod(Carbon::parse($date)->setTimezone('Europe/Riga')->addHours((int) $startHour[0])->addMinutes((int) $startHour[1]), Carbon::parse($date)->setTimezone('Europe/Riga')->addHours((int) $endTimechunks[0])->addMinutes((int) $endTimechunks[1]))->toArray();
            $timesWithoutBreak = array_values(array_diff($interval, $breakinterval));

            $bookingsRemoved = $bookings->map(function ($booking) use ($date, $timesWithoutBreak) {
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
                'isDayFree' => $daySettings->count() !== 0 ? $daySettings[0]->statuss === 1 ? true : false : true,
                'isDayVacation' => $isDayVacation->count() === 1 ? true : false,
                'start' => Carbon::parse($date)->setTimezone('Europe/Riga')->addHours((int) $startHour[0])->addMinutes((int) $startHour[1]),
                'end' => Carbon::parse($date)->setTimezone('Europe/Riga')->addHours((int) $endTimechunks[0])->addMinutes((int) $endTimechunks[1]),
                'interval' => $responseTimes->flatten()->filter(),

            ]);
        }


        return $times;

    }


    public function getspecialistapi(Request $request)
    {
        $id = $request->id;
        $user = User::find($id);
        return $user;
    }
}
