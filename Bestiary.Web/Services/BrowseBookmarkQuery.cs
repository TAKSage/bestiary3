using System.Text;
using Bestiary.Core.Models;
using Bestiary.Core.Services;

namespace Bestiary.Web.Services;

public sealed record BrowseBookmarkSnapshot(
    IReadOnlyList<string> Paths,
    bool AllRaces,
    IReadOnlyList<string> Races,
    string OrderPreset,
    string CustomOrder,
    bool Descending,
    bool NoImages,
    bool ShowCanBuild,
    bool ShowSystem,
    bool ShowAllDownloads,
    bool FilterExpanded);

/// <summary>Parses and builds <c>?p=…&amp;sort=…</c> bookmark URLs for the browse page.</summary>
public static class BrowseBookmarkQuery
{
    public const string Qp = "p";
    public const string Qr = "r";
    public const string Qsort = "sort";
    public const string Qkey = "key";
    public const string Qdesc = "desc";
    public const string Qni = "ni";
    public const string Qcb = "cb";
    public const string Qsys = "sys";
    public const string Qdl = "dl";
    public const string Qfold = "fold";

    const string TokName = "name";
    const string TokHealth = "health";
    const string TokW1 = "w1";
    const string TokCustom = "custom";

    static readonly string[] RaceOrder = ["Aramon", "Veruna", "Taros", "Zhon", "Creon"];

    public static string PresetToSortToken(string preset) =>
        preset switch
        {
            OrderPresets.UnitHealth => TokHealth,
            OrderPresets.Weapon1Damage => TokW1,
            OrderPresets.UnitName => TokName,
            _ => TokCustom
        };

    public static string SortTokenToPreset(string? token) =>
        token switch
        {
            TokHealth => OrderPresets.UnitHealth,
            TokW1 => OrderPresets.Weapon1Damage,
            TokName => OrderPresets.UnitName,
            _ => OrderPresets.UnitName
        };

    /// <summary>Returns null when there is no query string to apply.</summary>
    public static BrowseBookmarkSnapshot? TryParse(Uri uri, BestiaryManifest manifest)
    {
        if (!TryParseQuery(uri.Query, out var q) || q.Count == 0)
            return null;

        var pathLookup = manifest.SelectablePaths.ToDictionary(s => s.Path, s => s.Path, StringComparer.OrdinalIgnoreCase);

        var paths = new List<string>();
        if (q.TryGetValue(Qp, out var pvals))
        {
            foreach (var v in pvals)
            {
                if (string.IsNullOrEmpty(v)) continue;
                if (pathLookup.TryGetValue(v, out var canon))
                    paths.Add(canon);
            }
        }

        bool allRaces = true;
        var races = new List<string>();
        if (q.TryGetValue(Qr, out var rvals) && rvals.Count > 0)
        {
            var picked = new List<string>();
            var sawAll = false;
            foreach (var rv in rvals)
            {
                var s = rv.Trim();
                if (s.Length == 0) continue;
                if (s.Equals("all", StringComparison.OrdinalIgnoreCase))
                {
                    sawAll = true;
                    break;
                }
                var canon = RaceOrder.FirstOrDefault(r => r.Equals(s, StringComparison.OrdinalIgnoreCase));
                if (canon is not null)
                    picked.Add(canon);
            }
            if (sawAll || picked.Count == 0)
                allRaces = true;
            else
            {
                allRaces = false;
                races.AddRange(picked.Distinct(StringComparer.OrdinalIgnoreCase));
            }
        }

        var sortTok = TryGetFirst(q, Qsort) ?? TokName;

        string orderPreset;
        var customOrder = "";
        if (sortTok.Equals(TokCustom, StringComparison.OrdinalIgnoreCase))
        {
            orderPreset = "custom";
            var keyRaw = TryGetFirst(q, Qkey) ?? "";
            if (!OrderSpecUtility.TryNormalize(keyRaw, out var norm))
                norm = "";
            customOrder = norm;
        }
        else
        {
            orderPreset = SortTokenToPreset(sortTok);
        }

        var ds = TryGetFirst(q, Qdesc);
        var descending = ds is not null && (ds == "1" || ds.Equals("true", StringComparison.OrdinalIgnoreCase));

        var noImages = TryGetFirst(q, Qni) == "1";
        var showCanBuild = TryGetFirst(q, Qcb) == "1";
        var showSystem = TryGetFirst(q, Qsys) == "1";
        var showDl = TryGetFirst(q, Qdl) == "1";
        var collapsed = TryGetFirst(q, Qfold) == "1";

        return new BrowseBookmarkSnapshot(paths, allRaces, races, orderPreset, customOrder, descending,
            noImages, showCanBuild, showSystem, showDl, !collapsed);
    }

    static string? TryGetFirst(Dictionary<string, List<string>> q, string key) =>
        q.TryGetValue(key, out var list) && list.Count > 0 ? list[0] : null;

    /// <summary>Keys are lowercased for matching; values are URI-decoded.</summary>
    static bool TryParseQuery(string? query, out Dictionary<string, List<string>> q)
    {
        q = new Dictionary<string, List<string>>(StringComparer.OrdinalIgnoreCase);
        if (string.IsNullOrEmpty(query))
            return false;
        var s = query.StartsWith("?", StringComparison.Ordinal) ? query[1..] : query;
        if (s.Length == 0)
            return false;
        foreach (var part in s.Split('&'))
        {
            if (part.Length == 0) continue;
            var eq = part.IndexOf('=');
            ReadOnlySpan<char> rawK = eq >= 0 ? part.AsSpan(0, eq) : part.AsSpan();
            ReadOnlySpan<char> rawV = eq >= 0 ? part.AsSpan(eq + 1) : ReadOnlySpan<char>.Empty;
            var key = Uri.UnescapeDataString(rawK.ToString().Replace('+', ' '));
            var val = Uri.UnescapeDataString(rawV.ToString().Replace('+', ' '));
            if (key.Length == 0) continue;
            if (!q.TryGetValue(key, out var list))
            {
                list = new List<string>();
                q[key] = list;
            }
            list.Add(val);
        }

        return q.Count > 0;
    }

    public static string ToQuery(
        IReadOnlyCollection<string> paths,
        bool allRaces,
        IReadOnlyCollection<string> races,
        string orderPreset,
        string customOrder,
        bool descending,
        bool noImages,
        bool showCanBuild,
        bool showSystem,
        bool showAllDownloads,
        bool filterExpanded)
    {
        var sb = new StringBuilder();
        void Add(string k, string v)
        {
            if (sb.Length > 0) sb.Append('&');
            sb.Append(Uri.EscapeDataString(k)).Append('=').Append(Uri.EscapeDataString(v));
        }

        foreach (var p in paths.OrderBy(x => x, StringComparer.OrdinalIgnoreCase))
            Add(Qp, p);

        if (!allRaces && races.Count > 0)
        {
            foreach (var r in races.OrderBy(x => x, StringComparer.OrdinalIgnoreCase))
                Add(Qr, r);
        }

        if (orderPreset == "custom")
        {
            Add(Qsort, TokCustom);
            if (OrderSpecUtility.TryNormalize(customOrder, out var normKey) && normKey.Length > 0)
                Add(Qkey, normKey);
        }
        else
        {
            var tok = PresetToSortToken(orderPreset);
            if (tok != TokName)
                Add(Qsort, tok);
        }

        if (descending)
            Add(Qdesc, "1");
        if (noImages)
            Add(Qni, "1");
        if (showCanBuild)
            Add(Qcb, "1");
        if (showSystem)
            Add(Qsys, "1");
        if (showAllDownloads)
            Add(Qdl, "1");
        if (!filterExpanded)
            Add(Qfold, "1");

        return sb.ToString();
    }
}
