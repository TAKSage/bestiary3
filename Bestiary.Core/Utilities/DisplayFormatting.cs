using System.Globalization;
using System.Text.RegularExpressions;

namespace Bestiary.Core.Utilities;

public static class DisplayFormatting
{
    static readonly Regex TitleCaseOrdinalSuffix = new(@"\d+(St|Nd|Rd|Th)\b", RegexOptions.CultureInvariant | RegexOptions.Compiled);

    /// <summary>
    /// Title case for UI labels. System title-casing capitalizes the letter after digits (e.g. 3rd becomes 3Rd); this normalizes common English ordinal suffixes (st, nd, rd, th).
    /// </summary>
    public static string ToDisplayTitleCase(string? s, CultureInfo? culture = null)
    {
        if (string.IsNullOrEmpty(s))
            return s ?? "";
        culture ??= CultureInfo.CurrentCulture;
        var t = culture.TextInfo.ToTitleCase(s.ToLowerInvariant());
        return TitleCaseOrdinalSuffix.Replace(t, static m => m.Value[..^2] + m.Groups[1].Value.ToLowerInvariant());
    }
}
