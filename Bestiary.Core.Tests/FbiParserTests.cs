using Bestiary.Core.Fbi;

namespace Bestiary.Core.Tests;

public class FbiParserTests
{
    static readonly string SampleAraarch = """
[UNITINFO]
{
	acceleration = 10;
	name = Archer;
	unitname = ARAARCH;
	unitnumber = 1;
	description = Aramon;
	maxdamage = 1100;
}

[WEAPON1]
{
	name = Bow and Arrows;
	range = 450;
	[DAMAGE]
	{
		default = 213;
	}
}
""";

    [Fact]
    public void Parse_araarch_like_sample_populates_unitinfo_and_weapon()
    {
        var doc = FbiDocument.Parse(SampleAraarch);
        var ui = doc.UnitInfo!;
        Assert.Equal("Archer", ui["name"]);
        Assert.Equal("ARAARCH", ui["unitname"]);
        var w1 = doc.Root["WEAPON1"] as Dictionary<string, object?>;
        Assert.NotNull(w1);
        Assert.Equal("Bow and Arrows", w1!["name"]);
        var dmg = w1["DAMAGE"] as Dictionary<string, object?>;
        Assert.NotNull(dmg);
        Assert.Equal("213", dmg!["default"]?.ToString());
    }

    [Fact]
    public void Parse_real_araarch_fbi_from_repo()
    {
        var path = Path.Combine(FindRepoRoot(), "sets", "cavedog", "units", "araarch.fbi");
        Assert.True(File.Exists(path), $"Missing {path}");
        var text = File.ReadAllText(path);
        var doc = FbiDocument.Parse(text);
        Assert.NotNull(doc.UnitInfo);
        Assert.Equal("Archer", doc.UnitInfo!["name"]?.ToString());
        Assert.NotNull(doc.Root["WEAPON1"]);
    }

    [Fact]
    public void Parse_arapries_nested_adjustjoy_inside_unitinfo()
    {
        var path = Path.Combine(FindRepoRoot(), "sets", "cavedog", "units", "arapries.fbi");
        Assert.True(File.Exists(path));
        var doc = FbiDocument.Parse(File.ReadAllText(path));
        var adjust = doc.UnitInfo!["AdjustJoy"] as Dictionary<string, object?>;
        Assert.NotNull(adjust);
        Assert.Equal("250", adjust!["adjustment"]?.ToString());
        Assert.Equal("200", adjust["radius"]?.ToString());
    }

    static string FindRepoRoot()
    {
        var dir = new DirectoryInfo(AppContext.BaseDirectory);
        while (dir is not null)
        {
            var candidate = Path.Combine(dir.FullName, "sets", "cavedog", "units");
            if (Directory.Exists(candidate))
                return dir.FullName;
            dir = dir.Parent;
        }

        var fromCwd = Path.Combine(Directory.GetCurrentDirectory(), "sets", "cavedog", "units");
        if (Directory.Exists(fromCwd))
            return Directory.GetCurrentDirectory();

        throw new InvalidOperationException("Could not locate repo root with sets/cavedog/units.");
    }
}
