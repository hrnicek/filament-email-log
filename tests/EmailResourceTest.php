<?php

use Faker\Factory;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\artisan;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\travel;
use Hrnicek\FilamentEmailLog\Filament\Resources\EmailResource;
use Hrnicek\FilamentEmailLog\Models\Email;

it('redirects on non logged users', function () {
    $this->get(Config::get('filament.path'))
        ->assertRedirect();
});

it('shows dashboard to logged users', function () {
    actingAs($this->adminUser)->get(Config::get('filament.path'))->assertSuccessful();
});

it('can render EmailResourcePage', function () {
    actingAs($this->adminUser)->get(EmailResource::getUrl('index'))->assertSuccessful();
});

it('can display an email', function () {
    $faker = Factory::create();
    $recipient = $faker->email();
    $subject = $faker->words(5, asText: true);

    Mail::raw('Test e-mail text', function ($message) use ($recipient, $subject) {
        $message->to($recipient)
            ->subject($subject);
    });

    $email = Email::where('to', $recipient)->first();

    actingAs($this->adminUser)
        ->get(EmailResource::getUrl('view', $email->id))
        ->assertSee($recipient)
        ->assertSee($subject);
});

it('can purge old emails', function () {
    $faker = Factory::create();
    $recipient = $faker->email();
    $subject = $faker->words(5, asText: true);

    Mail::raw('Test e-mail text', function ($message) use ($recipient, $subject) {
        $message->to($recipient)
            ->subject($subject);
    });

    assertDatabaseHas(Email::class, [
        'to' => $recipient,
        'subject' => $subject,
    ]);

    travel(Config::get('filament-email-log.keep_email_for_days'))->days();

    artisan('model:prune', [
        '--model' => 'Hrnicek\FilamentEmailLog\Models\Email',
    ]);

    assertDatabaseMissing(Email::class, [
        'to' => $recipient,
        'subject' => $subject,
    ]);
});
