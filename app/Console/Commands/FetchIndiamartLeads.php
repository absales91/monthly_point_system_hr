<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Lead;
use GuzzleHttp\Client;

class FetchIndiamartLeads extends Command
{
    protected $signature = 'indiamart:fetch-leads';
    protected $description = 'Fetch leads from IndiaMART CRM API and store in leads table';

    public function handle()
{
    $client = new Client(['timeout' => 30]);

    $lastLead = Lead::orderBy('query_time', 'desc')->first();

    $startTime = ($lastLead && !empty($lastLead->query_time))
    ? Carbon::parse($lastLead->query_time)->format('d-M-Y')
    : Carbon::now()->subDay()->format('d-M-Y');


    $endTime = Carbon::now()->format('d-M-Y');

    $url = config('services.indiamart.url');

    try {
        $response = $client->request('POST', $url, [
            'query' => [
                'glusr_crm_key' => config('services.indiamart.key'),
                'start_time' => $startTime,
                'end_time' => $endTime,
            ]
        ]);

        $body = json_decode($response->getBody()->getContents(), true);
        // dd($body);

        if (!isset($body['STATUS']) || $body['STATUS'] !== 'SUCCESS') {
            $this->error('IndiaMART API error');
            return Command::FAILURE;
        }

        foreach ($body['RESPONSE'] as $item) {

            Lead::firstOrCreate(
                ['unique_query_id' => $item['UNIQUE_QUERY_ID']],
                [
                    'query_type'  => $item['QUERY_TYPE'] ?? null,
                    'query_time'  => $item['QUERY_TIME'] ?? null,
                    'name'        => $item['SENDER_NAME'] ?? null,
                    'mobile'      => $item['SENDER_MOBILE'] ?? null,
                    'email'       => $item['SENDER_EMAIL'] ?? null,
                    'company'     => $item['SENDER_COMPANY'] ?? null,
                    'city'        => $item['SENDER_CITY'] ?? null,
                    'state'       => $item['SENDER_STATE'] ?? null,
                    'country_iso' => $item['SENDER_COUNTRY_ISO'] ?? null,
                    'product'     => $item['QUERY_PRODUCT_NAME'] ?? null,
                    'message'     => strip_tags($item['QUERY_MESSAGE'] ?? ''),
                    'source'      => 'indiamart',
                    'lead_status' => 'new',
                ]
            );
        }

        $this->info('IndiaMART leads fetched successfully');

    } catch (\Exception $e) {
        $this->error($e->getMessage());
    }

    return Command::SUCCESS;
}

}
