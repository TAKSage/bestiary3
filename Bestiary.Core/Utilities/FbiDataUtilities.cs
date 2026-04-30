using System.Globalization;
using System.Text.Json;

namespace Bestiary.Core.Utilities;

public static class FbiDataUtilities
{
    public static object? JsonElementToObject(JsonElement element) =>
        element.ValueKind switch
        {
            JsonValueKind.Object => element.EnumerateObject()
                .ToDictionary(p => p.Name, p => JsonElementToObject(p.Value), StringComparer.OrdinalIgnoreCase),
            JsonValueKind.Array => throw new NotSupportedException("FBI JSON should not contain arrays."),
            JsonValueKind.String => element.GetString() ?? "",
            JsonValueKind.Number => element.GetRawText(),
            JsonValueKind.True => "1",
            JsonValueKind.False => "0",
            JsonValueKind.Null => null,
            _ => element.ToString()
        };

    public static Dictionary<string, object?> JsonElementToDictionary(JsonElement element)
    {
        if (element.ValueKind != JsonValueKind.Object)
            throw new InvalidOperationException("Expected JSON object at unit root.");
        return element.EnumerateObject()
            .ToDictionary(p => p.Name, p => JsonElementToObject(p.Value), StringComparer.OrdinalIgnoreCase);
    }

    public static string? GetString(Dictionary<string, object?>? dict, string key)
    {
        if (dict is null) return null;
        return dict.TryGetValue(key, out var v) ? v as string : null;
    }

    public static Dictionary<string, object?>? GetDict(object? o) => o as Dictionary<string, object?>;

    /// <summary>Navigate dot-separated path (e.g. UNITINFO.name or WEAPON1.DAMAGE.default).</summary>
    public static object? GetAtPath(Dictionary<string, object?> root, string dotted) =>
        GetAtPath(root, dotted.AsSpan());

    public static object? GetAtPath(Dictionary<string, object?> root, ReadOnlySpan<char> path)
    {
        object? cur = root;
        while (path.Length > 0)
        {
            var dot = path.IndexOf('.');
            ReadOnlySpan<char> segment = dot >= 0 ? path[..dot] : path;
            path = dot >= 0 ? path[(dot + 1)..] : ReadOnlySpan<char>.Empty;

            if (segment.Length == 0) return null;
            var seg = segment.ToString();
            if (cur is Dictionary<string, object?> d)
            {
                if (!d.TryGetValue(seg, out cur)) return null;
            }
            else return null;
        }
        return cur;
    }

    /// <summary>
    /// Compare two FBI field values for sorting: if both parse as numbers, compare numerically;
    /// otherwise case-insensitive ordinal string compare.
    /// </summary>
    public static int CompareValues(object? a, object? b, bool descending)
    {
        var asc = CompareValuesAscending(a, b);
        return descending ? -asc : asc;
    }

    static int CompareValuesAscending(object? a, object? b)
    {
        var sa = a?.ToString() ?? "";
        var sb = b?.ToString() ?? "";
        if (TryParseComparable(sa, out var da) && TryParseComparable(sb, out var db))
            return da.CompareTo(db);
        return string.Compare(sa, sb, StringComparison.OrdinalIgnoreCase);
    }

    static bool TryParseComparable(string s, out double d)
    {
        s = s.Trim();
        return double.TryParse(s, NumberStyles.Float, CultureInfo.InvariantCulture, out d);
    }
}
