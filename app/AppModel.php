<?php

class AppModel extends Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getContacts(string $sort_column = 'first_name', string $sort_type = 'asc'): array
    {

        $res = [];

        $contacts = DB::query("SELECT first_name, phone, dob FROM contacts ORDER BY $sort_column $sort_type");

        foreach ($contacts as $contact) {
            $res[] = array_values($contact);
        }

        return $res;

    }

    public function addContact(string $name, string $phone, string $date): bool
    {
        /*
         * INSERT IGNORE INTO contacts (first_name, phone, dob) VALUES('$name', '$phone', '$date')
         */

        return DB::insertIgnore('contacts', ['first_name' => $name, 'phone' => $phone, 'dob' => $date]) > 0;

    }

    public function deleteContact(string $phone): bool
    {

        /*
         * DELETE FROM contacts WHERE phone = '$phone'
         */

        return DB::delete('contacts', ['phone' => $phone]) > 0;

    }

}