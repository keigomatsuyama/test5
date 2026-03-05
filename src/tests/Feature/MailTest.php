<?php

namespace Tests\Feature;

use App\Mail\TestMail;
use Tests\TestCase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function メールが送信される()
    {
        Mail::fake();

        Mail::to('test@example.com')->send(new TestMail());

        Mail::assertSent(TestMail::class);
    }
}