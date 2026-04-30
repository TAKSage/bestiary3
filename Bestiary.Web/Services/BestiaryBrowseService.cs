using System.Net.Http.Json;
using System.Text.Json;
using Bestiary.Core.Models;
using Bestiary.Core.Utilities;

namespace Bestiary.Web.Services;

public sealed class BestiaryBrowseService(HttpClient http)
{
    BestiaryManifest? _manifest;

    public async Task<BestiaryManifest> GetManifestAsync(CancellationToken ct = default)
    {
        if (_manifest != null)
            return _manifest;
        var options = new JsonSerializerOptions { PropertyNameCaseInsensitive = true };
        var m = await http.GetFromJsonAsync<BestiaryManifest>("data/manifest.json", options, ct)
                 ?? throw new InvalidOperationException("manifest.json missing or invalid.");
        _manifest = m;
        return _manifest;
    }

    public void ClearManifestCache() => _manifest = null;

    public static async Task<IReadOnlyDictionary<string, Dictionary<string, object?>>> LoadChunkAsync(
        HttpClient http,
        string chunkRelativeUrl,
        CancellationToken ct = default)
    {
        await using var stream = await http.GetStreamAsync(chunkRelativeUrl, ct);
        using var doc = await JsonDocument.ParseAsync(stream, cancellationToken: ct);
        var units = doc.RootElement.GetProperty("units");
        var dict = new Dictionary<string, Dictionary<string, object?>>(StringComparer.OrdinalIgnoreCase);
        foreach (var p in units.EnumerateObject())
            dict[p.Name] = FbiDataUtilities.JsonElementToDictionary(p.Value);
        return dict;
    }
}
