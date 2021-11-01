<?php

class Valid
{
	protected function __construct()
	{
		//--
	}

    public static function normalizePhone(string $phone, bool $plus = false, bool $gaps = false): string
    {
        //--- we need only digits
        $phone = trim(preg_replace("/\D+/", '', $phone));
        if (!empty($phone) && (mb_strlen($phone) > 9)) {
            //--- format phone number
            if (preg_match('/^\d{10}$/', $phone)) {
                return ($plus ? '+' : '') . preg_replace("/(\d{3})(\d{3})(\d{2})(\d{2})/", ($gaps) ? '7 $1 $2 $3 $4' : '7$1$2$3$4', $phone);
            }
            if (preg_match('/^[78]\d{10}$/', $phone)) {
                return ($plus ? '+' : '') . preg_replace("/(\d)(\d{3})(\d{3})(\d{2})(\d{2})/", ($gaps) ? '7 $2 $3 $4 $5' : '7$2$3$4$5', $phone);
            }
        }
        return '';
    }

    public static function normalizeName(string $name): string
    {
        if (preg_match('/^[а-яё]{3,}$/ui', $name)) {
            return mb_strtoupper(mb_substr($name, 0, 1)) . mb_strtolower(mb_substr($name, 1));
        }

        return '';
    }

    public static function normalizeDate(string $date): string
    {

        if (($_date = strtotime($date)) && ($_date >= strtotime('1940-01-01'))) {
            return date('Y-m-d', $_date);
        }

        return '';
    }


}