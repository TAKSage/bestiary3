using System.Collections.Concurrent;
using System.Globalization;
using System.Text.Json;
using System.Text.Json.Nodes;
using Bestiary.Core.Fbi;
using Bestiary.Core.Models;

namespace Bestiary.Indexer;

public sealed class RepoIndexer
{
    static readonly HashSet<string> MediaExtensions = new(StringComparer.OrdinalIgnoreCase)
    {
        ".jpg", ".jpeg", ".gif", ".png", ".bmp", ".pcx"
    };

    static readonly HashSet<string> SkipExtensions = new(StringComparer.OrdinalIgnoreCase)
    {
        ".zip", ".exe", ".7z", ".rar", ".dll", ".pdb", ".git", ".php"
    };

    readonly string _repoRoot;
    readonly string _wwwroot;

    public RepoIndexer(string repoRoot, string wwwroot)
    {
        _repoRoot = Path.GetFullPath(repoRoot);
        _wwwroot = Path.GetFullPath(wwwroot);
    }

    public void Run()
    {
        Directory.CreateDirectory(Path.Combine(_wwwroot, "data", "chunks"));

        var selectablePaths = new List<SelectablePathInfo>();
        var setBundles = new Dictionary<string, SetResourceBundle>(StringComparer.OrdinalIgnoreCase);
        var chunkRefs = new List<UnitChunkRef>();

        foreach (var typeGroup in new[] { BestiaryConstants.SetsFolder, BestiaryConstants.ModsFolder, BestiaryConstants.RacesFolder })
        {
            var typeRoot = Path.Combine(_repoRoot, typeGroup);
            if (!Directory.Exists(typeRoot))
                continue;

            foreach (var setFolder in Directory.EnumerateDirectories(typeRoot))
            {
                var typeDirName = Path.GetFileName(setFolder);
                var setPathPosix = $"{typeGroup}/{typeDirName}".Replace('\\', '/');
                var map = BuildSetDirMap(setFolder);
                if (map.Count == 0)
                    continue;

                foreach (var (unitsKey, balanceLabel) in new[] { (BestiaryConstants.UnitsFolder, "Original TAK"), (BestiaryConstants.UnitsCbFolder, "Crusades Balance") })
                {
                    if (!map.TryGetValue(unitsKey, out var unitsDirName))
                        continue;
                    var unitsPath = $"{setPathPosix}/{unitsDirName}".Replace('\\', '/');
                    var fsUnits = Path.Combine(setFolder, unitsDirName);
                    if (!Directory.Exists(fsUnits))
                        continue;

                    selectablePaths.Add(new SelectablePathInfo
                    {
                        Path = unitsPath,
                        TypeGroup = typeGroup,
                        SetFolder = typeDirName,
                        BalanceLabel = balanceLabel
                    });

                    if (!setBundles.ContainsKey(setPathPosix))
                        setBundles[setPathPosix] = BuildSetBundle(setPathPosix, setFolder, map);

                    var chunkFileRel = $"data/chunks/{ChunkFileName(unitsPath)}.json";
                    var chunkPath = Path.Combine(_wwwroot, chunkFileRel.Replace('/', Path.DirectorySeparatorChar));
                    var count = WriteChunk(fsUnits, unitsPath, chunkPath);
                    chunkRefs.Add(new UnitChunkRef
                    {
                        ChunkFile = chunkFileRel.Replace('\\', '/'),
                        UnitsPath = unitsPath,
                        UnitCount = count
                    });
                }
            }
        }

        var downloads = ScanDownloads();
        CopyDownloads();
        CopyMediaTrees();
        CopyBannerImages();

        var cavedog = $"{BestiaryConstants.SetsFolder}/{BestiaryConstants.CavedogFolder}".Replace('\\', '/');
        var manifest = new BestiaryManifest
        {
            Version = "3.0-blazor",
            CavedogResourcePath = cavedog,
            SelectablePaths = selectablePaths,
            UnitChunks = chunkRefs,
            Sets = setBundles.ToDictionary(static kv => kv.Key, static kv => kv.Value, StringComparer.OrdinalIgnoreCase),
            Downloads = downloads.Count > 0 ? downloads : null
        };

        var opt = new JsonSerializerOptions { WriteIndented = true, PropertyNamingPolicy = JsonNamingPolicy.CamelCase };
        var manifestPath = Path.Combine(_wwwroot, "data", "manifest.json");
        File.WriteAllText(manifestPath, JsonSerializer.Serialize(manifest, opt));
        Console.WriteLine($"Wrote {manifestPath} ({selectablePaths.Count} selectable paths, {chunkRefs.Sum(c => c.UnitCount)} units).");
    }

    static string ChunkFileName(string unitsPath) =>
        string.Join('_', unitsPath.Split('/').Select(s => s.Replace(" ", "_")));

    /// <summary>Mirrors library.php set_dirs discovery for one set folder on disk.</summary>
    static Dictionary<string, string> BuildSetDirMap(string setFolderOnDisk)
    {
        var map = new Dictionary<string, string>(StringComparer.OrdinalIgnoreCase);
        foreach (var resDir in Directory.EnumerateDirectories(setFolderOnDisk))
        {
            var name = Path.GetFileName(resDir);
            if (string.IsNullOrEmpty(name))
                continue;
            map[name.ToLowerInvariant()] = name;
            if (name.Equals("anims", StringComparison.OrdinalIgnoreCase))
            {
                foreach (var picDir in Directory.EnumerateDirectories(resDir))
                {
                    var picName = Path.GetFileName(picDir);
                    map[picName.ToLowerInvariant()] = $"anims/{picName}".Replace('\\', '/');
                }
            }
        }
        return map;
    }

    SetResourceBundle BuildSetBundle(string setPathPosix, string setFolderOnDisk, IReadOnlyDictionary<string, string> map)
    {
        string SubPath(string key)
        {
            if (!map.TryGetValue(key, out var v))
                return "";
            return v;
        }

        var buildRoot = Path.Combine(setFolderOnDisk, SubPath("buildpic").Replace('/', Path.DirectorySeparatorChar));
        var weaponRoot = Path.Combine(setFolderOnDisk, SubPath("weaponpic").Replace('/', Path.DirectorySeparatorChar));
        var canbuildRoot = Path.Combine(setFolderOnDisk, SubPath("canbuild").Replace('/', Path.DirectorySeparatorChar));
        var canbuildCbRoot = Path.Combine(setFolderOnDisk, SubPath("canbuildcb").Replace('/', Path.DirectorySeparatorChar));

        return new SetResourceBundle
        {
            SetPath = setPathPosix,
            Buildpics = LoadFileMap(buildRoot),
            Weaponpics = LoadFileMap(weaponRoot),
            CanbuildFiles = LoadFileMap(canbuildRoot),
            CanbuildCbFiles = LoadFileMap(canbuildCbRoot),
            CanbuildMenus = LoadCanbuildMenus(canbuildRoot),
            CanbuildCbMenus = LoadCanbuildMenus(canbuildCbRoot)
        };
    }

    Dictionary<string, string> LoadFileMap(string dirOnDisk)
    {
        var d = new Dictionary<string, string>(StringComparer.OrdinalIgnoreCase);
        if (!Directory.Exists(dirOnDisk))
            return d;
        foreach (var file in Directory.EnumerateFiles(dirOnDisk))
        {
            var name = Path.GetFileName(file);
            d[name.ToLowerInvariant()] = GetRelativePathParts(_repoRoot, file);
        }
        return d;
    }

    static string GetRelativePathParts(string root, string full)
    {
        var rel = Path.GetRelativePath(root, full);
        return rel.Replace('\\', '/');
    }

    static Dictionary<string, List<string>> LoadCanbuildMenus(string canbuildRoot)
    {
        var menus = new Dictionary<string, List<string>>(StringComparer.OrdinalIgnoreCase);
        if (!Directory.Exists(canbuildRoot))
            return menus;
        foreach (var builderDir in Directory.EnumerateDirectories(canbuildRoot))
        {
            var stem = Path.GetFileName(builderDir).ToLowerInvariant();
            var items = Directory.EnumerateFiles(builderDir, "*.tdf")
                .Select(f => Path.GetFileNameWithoutExtension(f)!.ToLowerInvariant())
                .Distinct(StringComparer.OrdinalIgnoreCase)
                .ToList();
            menus[stem] = items;
        }
        return menus;
    }

    int WriteChunk(string fsUnitsDir, string unitsPathPosix, string outChunkPath)
    {
        var fbiFiles = Directory.EnumerateFiles(fsUnitsDir, "*.fbi").ToList();
        var unitsJson = new ConcurrentDictionary<string, JsonObject>(StringComparer.OrdinalIgnoreCase);

        Parallel.ForEach(fbiFiles, fbiFile =>
        {
            try
            {
                var text = File.ReadAllText(fbiFile);
                var doc = FbiDocument.Parse(text);
                var key = Path.Combine(unitsPathPosix, Path.GetFileName(fbiFile)).Replace('\\', '/').ToLowerInvariant();
                unitsJson[key] = doc.ToRootJsonObject();
            }
            catch (Exception ex)
            {
                Console.WriteLine($"Skip {fbiFile}: {ex.Message}");
            }
        });

        Directory.CreateDirectory(Path.GetDirectoryName(outChunkPath)!);
        var u = new JsonObject();
        foreach (var kv in unitsJson.OrderBy(x => x.Key, StringComparer.Ordinal))
            u[kv.Key] = kv.Value;
        var root = new JsonObject { ["units"] = u };
        var opt = new JsonSerializerOptions { WriteIndented = false, PropertyNamingPolicy = JsonNamingPolicy.CamelCase };
        File.WriteAllText(outChunkPath, root.ToJsonString(opt));
        return unitsJson.Count;
    }

    void CopyDownloads()
    {
        var src = Path.Combine(_repoRoot, "downloads");
        if (!Directory.Exists(src))
            return;
        foreach (var file in Directory.EnumerateFiles(src, "*", SearchOption.AllDirectories))
        {
            var ext = Path.GetExtension(file);
            if (SkipExtensions.Contains(ext))
                continue;
            var rel = Path.GetRelativePath(_repoRoot, file);
            var dest = Path.Combine(_wwwroot, rel);
            CopyFileIfNewer(file, dest);
        }
    }

    void CopyMediaTrees()
    {
        foreach (var typeGroup in new[] { BestiaryConstants.SetsFolder, BestiaryConstants.ModsFolder, BestiaryConstants.RacesFolder })
        {
            var src = Path.Combine(_repoRoot, typeGroup);
            if (!Directory.Exists(src))
                continue;
            foreach (var file in Directory.EnumerateFiles(src, "*", SearchOption.AllDirectories))
            {
                var ext = Path.GetExtension(file);
                if (SkipExtensions.Contains(ext))
                    continue;
                if (!MediaExtensions.Contains(ext))
                    continue;
                var rel = Path.GetRelativePath(_repoRoot, file);
                var dest = Path.Combine(_wwwroot, rel);
                CopyFileIfNewer(file, dest);
            }
        }
    }

    void CopyBannerImages()
    {
        var imgDir = Path.Combine(_repoRoot, "images");
        if (!Directory.Exists(imgDir))
            return;
        foreach (var file in Directory.EnumerateFiles(imgDir, "*", SearchOption.AllDirectories))
        {
            var ext = Path.GetExtension(file);
            if (SkipExtensions.Contains(ext))
                continue;
            if (!MediaExtensions.Contains(ext))
                continue;
            var rel = Path.GetRelativePath(_repoRoot, file);
            var dest = Path.Combine(_wwwroot, rel);
            CopyFileIfNewer(file, dest);
        }
    }

    static void CopyFileIfNewer(string src, string dest)
    {
        Directory.CreateDirectory(Path.GetDirectoryName(dest)!);
        if (!File.Exists(dest) || File.GetLastWriteTimeUtc(src) > File.GetLastWriteTimeUtc(dest))
            File.Copy(src, dest, overwrite: true);
    }

    List<DownloadCategory> ScanDownloads()
    {
        var list = new List<DownloadCategory>();
        var dl = Path.Combine(_repoRoot, "downloads");
        if (!Directory.Exists(dl))
            return list;
        foreach (var catDir in Directory.EnumerateDirectories(dl))
        {
            var catName = Path.GetFileName(catDir);
            var files = new List<DownloadFileInfo>();
            foreach (var file in Directory.EnumerateFiles(catDir))
            {
                var ext = Path.GetExtension(file);
                if (SkipExtensions.Contains(ext))
                    continue;
                var name = Path.GetFileName(file);
                var rel = Path.GetRelativePath(_repoRoot, file).Replace('\\', '/');
                files.Add(new DownloadFileInfo
                {
                    Name = name,
                    RelativeUrl = rel,
                    SizeText = FormatSize(new FileInfo(file).Length)
                });
            }
            if (files.Count > 0)
                list.Add(new DownloadCategory { Category = catName, Files = files });
        }
        return list;
    }

    static string FormatSize(long bytes)
    {
        string[] units = { "B", "KB", "MB", "GB" };
        double n = bytes;
        var i = 0;
        while (n >= 1024 && i < units.Length - 1)
        {
            n /= 1024;
            i++;
        }
        return Math.Round(n, 2).ToString(CultureInfo.InvariantCulture) + " " + units[i];
    }
}
