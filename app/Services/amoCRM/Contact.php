<?php

namespace App\Services\amoCRM;

use App\Models\Client;
use Exception;
use Ufee\Amo\Models\Contact as ContactModel;
use Ufee\Amo\Oauthapi;

class Contact
{
    private Oauthapi $amoApi;

    public function __construct(Oauthapi $amoApi)
    {
        $this->amoApi = $amoApi;
    }

    /**
     * @throws Exception
     */
    public function updateOrCreate(Client $client)
    {
        $contact = $this->searchContact($client);

        if ($contact) {

            $contact = $this->updateContact($contact, $client);
        } else {
            $contact = $this->createContact($client);
        }

        $client->contact_id = $contact->id;
        $client->save();

        return $contact;
    }

    /**
     * @throws Exception
     */
    private function searchContact(Client $client)
    {
        if ($client->phone) {

            $contacts = $this->amoApi
                ->contacts()
                ->searchByPhone($client->phone);
        }

        if (empty($contacts) && $client->email) {

            $contacts = $this->amoApi
                ->contacts()
                ->searchByEmail($client->email);
        }

        return !empty($contacts) ? $contacts->first() : null;
    }

    private function createContact(Client $client)
    {
        $contact = $this->amoApi->contacts()->create();

        $contact->name = $client->name;
        $contact->cf('Email')->setValue($client->email);
        $contact->cf('Телефон')->setValue($client->phone, 'Home');
        $contact->save();

        return $contact;
    }

    private function updateContact(ContactModel $contact, Client $client): ContactModel
    {
        $contact->cf('Телефон')->setValue($client->phone, 'Work');
        $contact->cf('Email')->setValue($client->email);
        $contact->save();

        return $contact;
    }
}
