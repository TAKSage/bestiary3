using Bestiary.Core.Models;

namespace Bestiary.Core.Services;

public static class MediaResolver
{
    public static string? ResolveBuildpic(string fileStem, string? unitName, SetResourceBundle set, SetResourceBundle cavedog)
    {
        string? Try(string stem)
        {
            var key = $"{stem}.jpg".ToLowerInvariant();
            if (set.Buildpics.TryGetValue(key, out var url))
                return url;
            if (cavedog.Buildpics.TryGetValue(key, out url))
                return url;
            return null;
        }

        return Try(fileStem) ?? (unitName != null ? Try(unitName) : null);
    }

    public static string? ResolveWeaponpic(string? buttonImageUp, SetResourceBundle set, SetResourceBundle cavedog)
    {
        if (string.IsNullOrEmpty(buttonImageUp))
            return null;
        var key = $"{buttonImageUp}.jpg".ToLowerInvariant();
        if (set.Weaponpics.TryGetValue(key, out var url))
            return url;
        if (cavedog.Weaponpics.TryGetValue(key, out url))
            return url;
        return null;
    }
}

public static class OrderPresets
{
    public const string UnitName = "UNITINFO.name";
    public const string UnitHealth = "UNITINFO.maxdamage";
    public const string Weapon1Damage = "WEAPON1.DAMAGE.default";
}
