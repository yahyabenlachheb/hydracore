<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    private const PER_PAGE = 25;

    public function index(Request $request): View
    {
        Gate::authorize('audit-logs.view');

        $filters = [
            'q' => trim((string) $request->query('q')),
            'user' => $request->query('user'),
            'table' => $request->query('table'),
            'action' => in_array($request->query('action'), ['insert', 'update', 'delete'], true) ? $request->query('action') : null,
            'from' => $this->date($request->query('from')),
            'to' => $this->date($request->query('to')),
        ];

        $logs = AuditLog::query()
            ->with('user:id,name,username')
            ->when($filters['q'] !== '', fn ($query) => $query->where('description', 'like', "%{$filters['q']}%"))
            ->when($filters['user'] === 'system', fn ($query) => $query->whereNull('user_id'))
            ->when(ctype_digit((string) $filters['user']), fn ($query) => $query->where('user_id', $filters['user']))
            ->when($filters['table'], fn ($query, $table) => $query->where('table_name', $table))
            ->when($filters['action'], fn ($query, $action) => $query->where('action', $action))
            ->when($filters['from'], fn ($query, $from) => $query->where('created_at', '>=', $from->startOfDay()))
            ->when($filters['to'], fn ($query, $to) => $query->where('created_at', '<=', $to->endOfDay()))
            ->latest('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('audit-logs.list', [
            'logs' => $logs,
            'filters' => $filters,
            'tables' => AuditLog::query()->distinct()->orderBy('table_name')->pluck('table_name'),
            'users' => User::whereIn('id', AuditLog::query()->whereNotNull('user_id')->distinct()->select('user_id'))->orderBy('name')->get(['id', 'name', 'username']),
        ]);
    }

    public function show(AuditLog $auditLog): View
    {
        Gate::authorize('audit-logs.view');

        $auditLog->load('user:id,name,username,email');

        return view('audit-logs.show', [
            'log' => $auditLog,
            'changes' => $this->changes($auditLog),
            'recordUrl' => $this->recordUrl($auditLog),
        ]);
    }

    /**
     * Pairs old and new values per field for the detail table.
     *
     * @return array<string, array{old: mixed, new: mixed}>
     */
    private function changes(AuditLog $log): array
    {
        $old = $log->old_values ?? [];
        $new = $log->new_values ?? [];
        $changes = [];

        foreach (array_unique([...array_keys($old), ...array_keys($new)]) as $field) {
            $changes[$field] = ['old' => $old[$field] ?? null, 'new' => $new[$field] ?? null];
        }

        return $changes;
    }

    /**
     * Link to the record's page, when it still exists and the app has one ("customers" → customers.show or customers.edit).
     */
    private function recordUrl(AuditLog $log): ?string
    {
        $base = Str::kebab(Str::camel($log->table_name));
        $route = Route::has("{$base}.show") ? "{$base}.show" : "{$base}.edit";

        if ($log->action === 'delete' || ! Route::has($route)) {
            return null;
        }

        $model = collect(glob(app_path('Models/*.php')))
            ->map(fn (string $file) => 'App\\Models\\' . basename($file, '.php'))
            ->first(fn (string $class) => is_subclass_of($class, Model::class) && (new $class)->getTable() === $log->table_name);

        return $model && $model::whereKey($log->record_id)->exists() ? route($route, $log->record_id) : null;
    }

    private function date(mixed $value): ?Carbon
    {
        try {
            return is_string($value) && $value !== '' ? Carbon::parse($value) : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
