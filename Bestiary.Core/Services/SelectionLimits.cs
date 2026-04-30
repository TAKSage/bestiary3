using Bestiary.Core.Models;

namespace Bestiary.Core.Services;

public static class SelectionLimits
{
    /// <summary>Matches index.php race / mod counting.</summary>
    public static (bool Ok, string? ErrorMessage) Validate(
        IReadOnlyList<string> selectedUnitPaths,
        IReadOnlyList<string> raceSelection)
    {
        var raceSelect = RaceWeight(raceSelection);
        var totalRaces = 0;
        var mods = 0;
        foreach (var path in selectedUnitPaths)
        {
            var parts = path.Split('/', StringSplitOptions.RemoveEmptyEntries | StringSplitOptions.TrimEntries);
            if (parts.Length < 2)
                continue;
            if (parts[0] is "sets" or "mods")
            {
                totalRaces += raceSelect;
                mods++;
            }
            else if (parts[0] == "races")
            {
                totalRaces += 1;
                mods++;
            }
        }

        if (totalRaces > BestiaryConstants.RacesLimit)
            return (false, $"Too many races selected ({totalRaces}); maximum is {BestiaryConstants.RacesLimit}.");
        if (mods > BestiaryConstants.ModsLimit)
            return (false, $"Too many sets/mods/races selected ({mods}); maximum is {BestiaryConstants.ModsLimit}.");
        return (true, null);
    }

    static int RaceWeight(IReadOnlyList<string> raceSelection)
    {
        if (raceSelection.Count == 0)
            return 5;
        if (raceSelection.Any(r => r.Equals("All", StringComparison.OrdinalIgnoreCase)))
            return 5;
        return raceSelection.Count;
    }
}
