<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Log;

class SpamKeywordRule implements Rule
{
    public function passes($attribute, $value): bool
    {
        // Your logic to check for spam keywords goes here
        // For example, you can have an array of spam keywords and check if the value contains any of them

        $spamKeywords = [
            'make money',
            'private photos',
            'earn $100',
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
            'Respond with yes',
            'If this interests you, respond to this email with a YES.'
        ];

        foreach ($spamKeywords as $keyword) {
            if (stripos(strtolower($value), strtolower($keyword)) !== false && str($value)->transliterate()->contains($keyword) !== false){
                try{
                    Log::channel('spam-keyword')->info('Spam Keyword Detected: ' . $keyword . ' in ' . $value);
                } catch (\Exception $e) {
                    Log::error('Error logging spam keyword: ' . $e->getMessage());
                }
                return false;
            }
        }

        return true;
    }

    public function message(): string
    {
        return 'Invalid input detected. Please avoid using certain keywords in your :attribute.';
    }
}
