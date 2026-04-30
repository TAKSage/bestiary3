namespace Bestiary.Core.Models;

public sealed class DownloadCategory
{
    public string Category { get; set; } = "";
    public List<DownloadFileInfo> Files { get; set; } = new();
}

public sealed class DownloadFileInfo
{
    public string Name { get; set; } = "";
    public string RelativeUrl { get; set; } = "";
    public string SizeText { get; set; } = "";
}

public sealed class BestiaryManifest
{
    public string Version { get; set; } = "";
    public string CavedogResourcePath { get; set; } = "";
    public List<SelectablePathInfo> SelectablePaths { get; set; } = new();
    public List<UnitChunkRef> UnitChunks { get; set; } = new();
    public Dictionary<string, SetResourceBundle> Sets { get; set; } = new(StringComparer.OrdinalIgnoreCase);
    public List<DownloadCategory>? Downloads { get; set; }
}

public sealed class SelectablePathInfo
{
    public string Path { get; set; } = "";
    public string TypeGroup { get; set; } = "";
    public string SetFolder { get; set; } = "";
    public string BalanceLabel { get; set; } = "";
}

public sealed class UnitChunkRef
{
    public string ChunkFile { get; set; } = "";
    public string UnitsPath { get; set; } = "";
    public int UnitCount { get; set; }
}

/// <summary>Per-set paths for resolving images and can-build menus (mirrors PHP set_dirs + maps).</summary>
public sealed class SetResourceBundle
{
    public string SetPath { get; set; } = "";
    public Dictionary<string, string> Buildpics { get; set; } = new(StringComparer.OrdinalIgnoreCase);
    public Dictionary<string, string> Weaponpics { get; set; } = new(StringComparer.OrdinalIgnoreCase);
    public Dictionary<string, string> CanbuildFiles { get; set; } = new(StringComparer.OrdinalIgnoreCase);
    public Dictionary<string, string> CanbuildCbFiles { get; set; } = new(StringComparer.OrdinalIgnoreCase);
    public Dictionary<string, List<string>> CanbuildMenus { get; set; } = new(StringComparer.OrdinalIgnoreCase);
    public Dictionary<string, List<string>> CanbuildCbMenus { get; set; } = new(StringComparer.OrdinalIgnoreCase);
}

/// <summary>JSON shape written per chunk file under wwwroot/data/chunks/</summary>
public sealed class UnitChunkFileDto
{
    /// <summary>Lowercase FBI path -> unit root object (UNITINFO, WEAPON1, ...).</summary>
    public Dictionary<string, System.Text.Json.JsonElement> Units { get; set; } = new(StringComparer.OrdinalIgnoreCase);
}

/// <summary>Flattened unit row for Blazor (pre-merged chunk payloads at runtime).</summary>
public sealed class UnitViewModel
{
    public required string Key { get; init; }
    /// <summary>UNITINFO, WEAPON1, … each maps to dictionary tree (string leaves).</summary>
    public required Dictionary<string, object?> Data { get; init; }
    public required string UnitsPath { get; init; }
    public required string SetPath { get; init; }
    public required string SetDisplayName { get; init; }
    public required string Balance { get; init; }
    public required string FileStem { get; init; }
    public required bool IsCrusadesBalance { get; init; }
}
