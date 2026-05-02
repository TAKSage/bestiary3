using System.Text;

namespace Bestiary.Core.Fbi;

/// <summary>Parses Total Annihilation / Kingdoms FBI text into nested string dictionaries (PHP bestiary semantics).</summary>
public static class FbiParser
{
    public static Dictionary<string, object?> Parse(string text)
    {
        var pos = 0;
        SkipWhitespaceAndComments(text, ref pos);
        var root = new Dictionary<string, object?>(StringComparer.OrdinalIgnoreCase);
        while (pos < text.Length)
        {
            if (text[pos] != '[')
                throw new InvalidDataException($"Expected '[' at position {pos}.");
            var section = ParseSectionHeader(text, ref pos);
            var block = ParseBlock(text, ref pos);
            root[section] = block;
            SkipWhitespaceAndComments(text, ref pos);
        }
        return root;
    }

    static string ParseSectionHeader(string text, ref int pos)
    {
        pos++; // [
        var sb = new StringBuilder();
        while (pos < text.Length && text[pos] != ']')
        {
            sb.Append(text[pos]);
            pos++;
        }
        if (pos >= text.Length) throw new InvalidDataException("Unclosed section header.");
        pos++; // ]
        var name = sb.ToString().Trim();
        if (name.Length == 0) throw new InvalidDataException("Empty section name.");
        return name;
    }

    static Dictionary<string, object?> ParseBlock(string text, ref int pos)
    {
        SkipWhitespaceAndComments(text, ref pos);
        if (pos >= text.Length || text[pos] != '{')
            throw new InvalidDataException($"Expected '{{' at position {pos}.");
        pos++;
        var dict = new Dictionary<string, object?>(StringComparer.OrdinalIgnoreCase);
        while (true)
        {
            SkipWhitespaceAndComments(text, ref pos);
            if (pos >= text.Length) throw new InvalidDataException("Unclosed block.");
            if (text[pos] == '}')
            {
                pos++;
                break;
            }
            if (text[pos] == '[')
            {
                var nestedSection = ParseSectionHeader(text, ref pos);
                var nestedBlock = ParseBlock(text, ref pos);
                dict[nestedSection] = nestedBlock;
            }
            else
            {
                var key = ParseIdentifier(text, ref pos);
                SkipWhitespaceAndComments(text, ref pos);
                if (pos >= text.Length || text[pos] != '=')
                    throw new InvalidDataException($"Expected '=' after key '{key}' at position {pos}.");
                pos++;
                SkipWhitespaceAndComments(text, ref pos);
                var value = ParseValue(text, ref pos);
                dict[key] = value;
            }
        }
        return dict;
    }

    static string ParseIdentifier(string text, ref int pos)
    {
        var sb = new StringBuilder();
        while (pos < text.Length && (char.IsLetterOrDigit(text[pos]) || text[pos] == '_'))
        {
            sb.Append(text[pos]);
            pos++;
        }
        var id = sb.ToString();
        if (id.Length == 0) throw new InvalidDataException($"Expected identifier at position {pos}.");
        return id;
    }

    static string ParseValue(string text, ref int pos)
    {
        var sb = new StringBuilder();
        while (pos < text.Length && text[pos] != ';')
        {
            sb.Append(text[pos]);
            pos++;
        }
        if (pos >= text.Length || text[pos] != ';')
            throw new InvalidDataException("Expected ';' after value.");
        pos++;
        return sb.ToString().Trim();
    }

    static void SkipWhitespaceAndComments(string text, ref int pos)
    {
        while (pos < text.Length)
        {
            var c = text[pos];
            if (char.IsWhiteSpace(c))
            {
                pos++;
                continue;
            }
            if (pos + 1 < text.Length && c == '/' && text[pos + 1] == '/')
            {
                pos += 2;
                while (pos < text.Length && text[pos] != '\r' && text[pos] != '\n')
                    pos++;
                continue;
            }
            break;
        }
    }
}
