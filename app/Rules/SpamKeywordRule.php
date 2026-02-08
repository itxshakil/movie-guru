<?php

declare(strict_types=1);

namespace App\Rules;

use Exception;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Log;

final class SpamKeywordRule implements Rule
{
    public function passes($attribute, $value): bool
    {
        $spamKeywords = [
            'make money',
            'private photos',
            'earn $100',
            'Double your income',
            'Get paid',
            'Get rich',
            'Make money fast',
            'Make money now',
            'Make money today',
            'Make money while you sleep',
            'Make money with no investment',
            'Make money with no work',
            'Make money with your computer',
            'Make money with your email address',
            'Make money with your home computer',
            'Make money with your PC',
            'Financial freedom',
            'Free money',
            'Earn extra cash',
            'Enjoy lots of targeted traffic to your site for free!',
            'QUICK WAY TO MAKE MONEY',
            'Receipt of money to your account',
            'Your account has been replenished ',
            'Your account has been credited',
            'Get your website to Google first page',
            'Your website has been approved for submission to our directory',
            'Unpublished private photos',
            'money',
            'earn',
            'profit',
            'limited offer',
            'website design',
            'marketing',
            'directory submission',
            'free traffic',
            'approved',
            'submission',
            'leads',
            'patent',
            'trademark',
            'tax season',
            'private AI robot',
            'unpublished private photos',
            'naked Kim Kardashian',
            'google listing',
            'If this interests you, respond to this email with a YES.',
            'Respond with YES',
            'Respond with YES to this email',
            'Respond with YES to this email and we will send you more information',
            'Get It For FREE',
            'Foolproof Money Making',
            'Millions Online',
            'Once in a Lifetime',
            'No Work Required',
            'steamy movie',
            'steamy sauna session',
            'your account has been dormant',
            'visit our telegram group',
        ];

        foreach ($spamKeywords as $spamKeyword) {
            if (mb_stripos(mb_strtolower((string)$value), mb_strtolower($spamKeyword)) === false) {
                continue;
            }

            try {
                Log::channel('spam-keyword')->info('Spam Keyword Detected: ' . $spamKeyword . ' in ' . $value);
            } catch (Exception $e) {
                Log::error('Error logging spam keyword: ' . $e->getMessage());
            }

            return false;
        }

        return true;
    }

    public function message(): string
    {
        return 'Invalid input detected. Please avoid using certain keywords in your :attribute.';
    }
}
