<?php

namespace App\TripCalculate;

use DateTime;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class TripCalculator
{

    private function calculateAge(\DateTime $birthDate, \DateTime $startDate): int
    {
        $interval = $startDate->diff($birthDate);
        return $interval->y;
    }

    public function calculateCost(array $data)
    {
        if (!isset($data['price']) || !isset($data['birthDate'])) {
            throw new BadRequestHttpException('Missing required fields');
        }

        $price = floatval($data['price']);
        if($price<10000){
            return [
                'originalPrice' => $price,
                'birthDiscount' => '0',
                'earlyBookingDiscount' => '0',
                'finalPrice' => $price
            ];
        } else {
            $birthDate = new DateTime($data['birthDate']);
            if (!isset($data['birthDate'])){
                $startDate = new DateTime();
            }else {
                $startDate = new DateTime($data['bookingDate']);
            }
            $yearOfUser = $this->calculateAge($birthDate, $startDate);
            if($yearOfUser<18){
                $discount = $this->calculateChildrenDiscountPrice($yearOfUser, $price);
                $price -= $discount;
            }
            if(!isset($data['payDate'])){
                $finalPrice = $price;
                $earlyBookingDiscount = 0;
            } else {
                $payDate = new DateTime($data['payDate']);
                $earlyBookingDiscount = $this->calculateEarlyBookingDiscount($startDate, $payDate, $price);
                $finalPrice = $price - $earlyBookingDiscount;
            }

            return [
                'originalPrice' => $price,
                'childrenDiscount' => $discount,
                'earlyBookingDiscount' => $earlyBookingDiscount,
                'finalPrice' => $finalPrice
            ];
        }
    }

    private function calculateChildrenDiscountPrice($year, $price): float
    {
        if ($year < 3) {
            return 0.8 * $price;
        } elseif ($year < 6) {
            return min(0.3*$price, 4500);
        } else {
            return 0.1*$price;
        }
    }

    private function calculateEarlyBookingDiscount(DateTime $startDate, DateTime $payDate, $price): float
    {
        $currentYear = (int)$payDate->format('Y');
        $currentMonth = (int)$payDate->format('m');
        $currentDay = (int)$startDate->format('d');

        $startDay = (int)$startDate->format('d');
        $startYear = (int)$startDate->format('Y');
        $startMonth = (int)$startDate->format('m');

        $discount = $this->calculateDiscount($currentYear, $currentMonth, $startYear, $startMonth, $currentDay, $startDay, $price);

        return min(1500, $discount);
    }

    private  function calculateDiscount(int $year, int $month, $startYear, $startMonth, $day, $startDay, $price): float
    {
        $discount = $this->getDiscount($startYear, $startMonth, $startDay, $year, $month, $day);
        $discoun_in_price = $price*$discount;
        return $discoun_in_price;
    }

    private  function getDiscount(int $year, int $month, int $day, $payYear, $payMonth, $payDay): int
    {
        if ($year === date('Y')) {
            if ($month >= 10 && $month <= 12) {
                if($payMonth <= 3 && $payYear === date('Y')){
                    return 0.07;
                } elseif ($payMonth == 4 && $payYear === date('Y')){
                    return 0.05;
                }elseif ($payMonth == 5 && $payYear === date('Y')){
                    return 0.03;
                }
            }
        } elseif ($year === date('Y+1')) {
            if ($month >= 4 && $month <= 9) {
                if($payMonth <= 11 && $payYear === date('Y')){
                    return 0.07;
                } elseif ($payMonth == 12 && $payYear === date('Y')){
                    return 0.05;
                }elseif ($payMonth == 1 && $payYear === date('Y+1')){
                    return 0.03;
                }
            } elseif ($month == 1 && $day <= 14) {
                if($payMonth <= 3 && $payYear === date('Y')){
                    return 0.07;
                } elseif ($payMonth == 4 && $payYear === date('Y')){
                    return 0.05;
                }elseif ($payMonth == 5 && $payYear === date('Y')){
                    return 0.03;
                }
            }
            elseif ($month >= 1 && $day >= 15) {
                if($payMonth <= 8 && $payYear === date('Y')){
                    return 0.07;
                } elseif ($payMonth == 9 && $payYear === date('Y')){
                    return 0.05;
                }elseif ($payMonth == 10 && $payYear === date('Y')){
                    return 0.03;
                }
            }
        }
        return 0;
    }


}