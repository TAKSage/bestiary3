using System.Text.RegularExpressions;

namespace Bestiary.Web.Services;

public static partial class OrderSpecUtility
{
    /// <summary>Accept PHP-style or dotted paths; returns false if unsafe characters.</summary>
    public static bool TryNormalize(string? raw, out string dotted)
    {
        dotted = "";
        if (string.IsNullOrWhiteSpace(raw))
            return false;
        var s = raw.Trim();
        s = SectionRegex().Replace(s, ".$1");
        s = s.Replace("[", "").Replace("]", "").Replace("'", "");
        while (s.Contains("..", StringComparison.Ordinal))
            s = s.Replace("..", ".", StringComparison.Ordinal);
        dotted = string.Join('.', s.Split('.', StringSplitOptions.RemoveEmptyEntries | StringSplitOptions.TrimEntries));
        if (dotted.Length == 0)
            return false;
        foreach (var part in dotted.Split('.'))
        {
            if (part.Length == 0 || !part.All(c => char.IsLetterOrDigit(c) || c == '_'))
                return false;
        }
        return true;
    }

    [GeneratedRegex(@"\[\s*'([^']+)'\s*\]", RegexOptions.Compiled)]
    private static partial Regex SectionRegex();
}
