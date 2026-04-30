using System.Text.Json.Nodes;

namespace Bestiary.Core.Fbi;

public sealed class FbiDocument
{
    public Dictionary<string, object?> Root { get; }

    public FbiDocument(Dictionary<string, object?> root) => Root = root;

    public Dictionary<string, object?>? UnitInfo => Root.GetValueOrDefault("UNITINFO") as Dictionary<string, object?>;

    public static FbiDocument Parse(string fbiText) =>
        new(FbiParser.Parse(fbiText));

    public static JsonObject ToJsonObject(Dictionary<string, object?> section)
    {
        var o = new JsonObject();
        foreach (var kv in section)
            o[kv.Key] = ToJsonNode(kv.Value);
        return o;
    }

    static JsonNode ToJsonNode(object? value) =>
        value switch
        {
            Dictionary<string, object?> nested => ToJsonObject(nested),
            null => JsonValue.Create(string.Empty)!,
            var x => JsonValue.Create(x.ToString() ?? "")!
        };

    public JsonObject ToRootJsonObject()
    {
        var root = new JsonObject();
        foreach (var kv in Root)
            root[kv.Key] = kv.Value is Dictionary<string, object?> d ? ToJsonObject(d) : ToJsonNode(kv.Value);
        return root;
    }
}
