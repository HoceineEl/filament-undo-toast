<?php

declare(strict_types=1);

namespace Workbench\Database\Seeders;

use Illuminate\Database\Seeder;
use Workbench\App\Models\Customer;
use Workbench\App\Models\Project;
use Workbench\App\Models\Tag;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        collect([
            ['Acme Corporation', 'hello@acme.test', 'Casablanca'],
            ['Blue Harbor Studio', 'team@blueharbor.test', 'Lisbon'],
            ['Cedar & Co', 'contact@cedar.test', 'Beirut'],
            ['Dune Logistics', 'ops@dune.test', 'Marrakech'],
            ['Evergreen Clinic', 'care@evergreen.test', 'Lyon'],
            ['Falcon Air', 'fly@falcon.test', 'Dubai'],
        ])->each(fn (array $customer): Customer => Customer::create([
            'name' => $customer[0],
            'email' => $customer[1],
            'city' => $customer[2],
        ]));

        $tags = collect(['Urgent', 'Design', 'Backend', 'Research', 'Launch'])
            ->map(fn (string $name): Tag => Tag::create(['name' => $name]));

        collect(['Website redesign', 'Mobile app', 'Billing migration'])
            ->each(function (string $name, int $index) use ($tags): void {
                $project = Project::create(['name' => $name]);

                $project->tags()->attach($tags[$index]->id, ['role' => 'primary']);
                $project->tags()->attach($tags[$index + 1]->id, ['role' => 'secondary']);

                $project->tasks()->createMany([
                    ['title' => 'Kick-off', 'done' => true],
                    ['title' => 'First review'],
                    ['title' => 'Ship it'],
                ]);
            });
    }
}
