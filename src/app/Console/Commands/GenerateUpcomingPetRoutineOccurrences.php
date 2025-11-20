<?php

namespace App\Console\Commands;

use App\Models\PetRoutine;
use App\Services\Pet\PetRoutineOccurrenceService;
use Illuminate\Console\Command;

/**
 * Artisan command to generate upcoming routine occurrences.
 */
class GenerateUpcomingPetRoutineOccurrences extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pet:routines:generate-upcoming {days=7 : Number of future days to generate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate upcoming PetRoutineOccurrence records for configured pet routines.';

    public function handle(PetRoutineOccurrenceService $service): int
    {
        $days = (int) $this->argument('days');
        $count = 0;
        $routines = PetRoutine::query()->get();
        foreach ($routines as $routine) {
            $created = $service->generateUpcoming($routine, $days);
            $count += $created->count();
        }
        $this->info("Generated {$count} upcoming occurrences (next {$days} days).");
        return self::SUCCESS;
    }
}
