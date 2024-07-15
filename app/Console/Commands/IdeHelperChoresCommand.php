<?php

/** @noinspection PhpUnusedPrivateMethodInspection */

declare(strict_types=1);

namespace App\Console\Commands;

use App\Rules\Rule;
use App\Support\Discover;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Collection;
use ReflectionMethod;
use ReflectionObject;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class IdeHelperChoresCommand extends Command
{
    private const SUFFIX = 'Chore';

    protected $signature = 'ide-helper:chores
        {--only=* : Only output chores with the given name}
        {--except=* : Do not output chores with the given name}
        {--json : Output as JSON.}
    ';

    protected $description = 'Generate chores for the Laravel-Idea-JSON file.';

    public function isEnabled(): bool
    {
        return $this->laravel->isLocal();
    }

    /**
     * @noinspection PhpMemberCanBePulledUpInspection
     *
     * @throws \ReflectionException
     */
    public function handle(): void
    {
        collect((new ReflectionObject($this))->getMethods())
            ->filter(static fn (ReflectionMethod $method): bool => str($method->name)->endsWith(self::SUFFIX))
            ->when(
                $this->option('only'),
                static fn (Collection $methods, array $only): Collection => $methods->filter(
                    static fn (ReflectionMethod $method): bool => str($method->name)->is($only)
                )
            )
            ->when(
                $this->option('except'),
                static fn (Collection $methods, array $except): Collection => $methods->reject(
                    static fn (ReflectionMethod $method): bool => str($method->name)->is($except)
                )
            )
            ->sortBy(static fn (ReflectionMethod $method): string => $method->name)
            ->each(
                fn (ReflectionMethod $method): mixed => $method->isPublic()
                    ? $this->laravel->call([$this, $method->name])
                    : $method->invoke($this)
            )
            ->whenEmpty(fn (Collection $methods) => $this->output->warning('No chores found.'))
            ->whenNotEmpty(fn (Collection $methods) => $this->output->success("Generated {$methods->count()} chores."));
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void {}

    protected function rules(): array
    {
        return [
            'only' => 'array',
            'only.*' => 'string',
            'except' => 'array',
            'except.*' => 'string',
            'json' => 'bool',
        ];
    }

    public function routeUriChore(Router $router): void
    {
        collect($router->getRoutes())
            ->map(static fn (Route $route): string => $route->uri())
            ->unique()
            ->sort()
            ->values()
            ->tap(fn (Collection $routeUris) => $this->output($routeUris));
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function ruleChore(): void
    {
        Discover::in('Rules')
            ->instanceOf(Rule::class)
            ->all()
            ->map(static fn (\ReflectionClass $ruleReflectionClass, $ruleClass): string => $ruleClass::name())
            ->sort()
            ->values()
            ->tap(fn (Collection $rules) => $this->output($rules));
    }

    /**
     * @noinspection DebugFunctionUsageInspection
     */
    private function output(Collection $chore): void
    {
        $trace = collect(debug_backtrace())->first(
            static function (array $trace): bool {
                $trace += [
                    'class' => null,
                    'type' => null,
                    'function' => null,
                ];

                return $trace['class'] === self::class
                    && $trace['type'] === '->'
                    && str($trace['function'])->endsWith(self::SUFFIX);
            },
            ['function' => null]
        );

        $this->output->warning(sprintf(
            'Found %s %s:',
            $chore->count(),
            str($trace['function'])->remove(self::SUFFIX)->plural()->snake(' ')
        ));

        $this->option('json')
            ? $this->line($chore->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_LINE_TERMINATORS))
            : $this->output->listing($chore->all());
    }
}
