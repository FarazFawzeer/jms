<?php

namespace App\Support;

use App\Models\Division as DivisionModel;
use App\Models\User;

/**
 * Who sees which division's data. Used by every stock / sales / report module.
 *
 *   - Each user belongs to one division (users.stock_loc_sys_code -> pStock_Location_Tab).
 *   - A division marked "Overall" (Is_Main = 1) sees every division.
 *   - A Super Admin with no division also sees every division.
 *   - Anyone else sees and enters only their own division's records.
 *   - A non-admin user with no division sees nothing until an admin assigns one.
 */
class Division
{
    private static array $cache = [];

    public static function user(): ?User
    {
        return auth()->user();
    }

    /** The logged-in user's division code ('' when none). */
    public static function code(?User $user = null): string
    {
        $user ??= self::user();

        return trim((string) $user?->stock_loc_sys_code);
    }

    /** True when this user sees all divisions (Overall division, or Super Admin without a division). */
    public static function seesAll(?User $user = null): bool
    {
        $user ??= self::user();
        if (! $user) {
            return true; // console, queue, seeders: no filtering
        }

        $code = self::code($user);
        $key = $user->getKey() . '|' . $code;

        return self::$cache[$key] ??= $code === ''
            ? $user->type === 'Super Admin'
            : DivisionModel::where('stock_loc_sys_code', $code)->where('Is_Main', true)->exists();
    }

    /** Filter a query to the divisions this user may see. */
    public static function apply($query, string $column, ?User $user = null)
    {
        if (self::seesAll($user)) {
            return $query;
        }

        $code = self::code($user);

        return $code === ''
            ? $query->whereRaw('1 = 0')
            : $query->where($column, $code);
    }

    /** Division to save a new record under: the user's own, or the one an Overall user picked. */
    public static function forSaving(?string $picked = null, ?User $user = null): ?string
    {
        if (self::seesAll($user)) {
            return trim((string) $picked) ?: null;
        }

        return self::code($user) ?: null;
    }

    /** Divisions that hold stock (not Overall), for dropdowns and filters. */
    public static function options(array $keep = [])
    {
        return DivisionModel::query()
            ->where('Is_Main', false)
            ->where(fn ($q) => $q->where('status', '<>', 'I')->orWhereIn('stock_loc_sys_code', $keep ?: ['']))
            ->orderBy('stock_loc_user_code')
            ->get();
    }

    /** ['1' => 'MAIN STORS', ...] for showing names in lists. */
    public static function names(): array
    {
        return self::$cache['names'] ??= DivisionModel::query()
            ->get(['stock_loc_sys_code', 'stock_loc_desc'])
            ->mapWithKeys(fn ($d) => [trim($d->stock_loc_sys_code) => trim((string) $d->stock_loc_desc)])
            ->all();
    }

    /** Short text for page headers: "All divisions" or the user's division name. */
    public static function label(?User $user = null): string
    {
        if (self::seesAll($user)) {
            return 'All divisions';
        }

        $code = self::code($user);

        return $code === '' ? 'No division assigned' : (self::names()[$code] ?? $code);
    }

    public static function flush(): void
    {
        self::$cache = [];
    }
}
