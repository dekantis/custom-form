<?php
declare(strict_types=1);

class CrmApi
{
    const CRM_API_URL = 'https://api.hubapi.com/crm/v3/objects/contacts/';
    const CRM_API_AUTH_TOKEN = 'Authorization: Bearer pat-eu1-9939df97-2220-4a01-bbf7-1e6c74677520';

    private string $firstname;
    private string $lastname;
    private string $email;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->firstname = $data['firstname'];
        $this->lastname = $data['lastname'];
        $this->email = $data['email'];
    }

    /**
     * @return array
     */
    public function createContact(): array
    {
        $postData['properties']['firstname'] = $this->firstname;
        $postData['properties']['lastname'] = $this->lastname;
        $postData['properties']['email'] = $this->email;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::CRM_API_URL);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', self::CRM_API_AUTH_TOKEN));

        $response = curl_exec($ch);

        return json_decode($response, true);
    }
}
