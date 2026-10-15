<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Mail\AdminDigestMail;
use App\Services\AdminDigestService;
use App\Support\LogCommands;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'admin:digest', description: 'Send the weekly or monthly admin activity digest')]
final class SendAdminDigestCommand extends Command
{
    use LogCommands;

    protected $signature = 'admin:digest {type=weekly : The digest period (weekly or monthly)}';

    public function __construct(private readonly AdminDigestService $adminDigestService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $type = (string) $this->argument('type');

        if (! in_array($type, ['weekly', 'monthly'], strict: true)) {
            $this->logError('Invalid digest type. Use "weekly" or "monthly".');

            return self::INVALID;
        }

        $adminAddress = config('mail.admin.address');

        if (blank($adminAddress)) {
            $this->logError('Admin mail address is not configured; skipping admin digest.');

            return self::FAILURE;
        }

        $end = CarbonImmutable::now();
        $start = $type === 'weekly' ? $end->subWeek() : $end->subMonth();

        $data = $this->adminDigestService->generate($start, $end, $type);

        Mail::to($adminAddress, config('mail.admin.name'))->queue(new AdminDigestMail($data));

        $this->log(sprintf(
            'Sent %s admin digest (%s - %s) to %s.',
            $type,
            $start->toDateString(),
            $end->toDateString(),
            $adminAddress,
        ));

        return self::SUCCESS;
    }
}
