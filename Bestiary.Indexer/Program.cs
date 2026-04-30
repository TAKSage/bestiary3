using Bestiary.Indexer;

var repo = "";
var wwwroot = "";
for (var i = 0; i < args.Length; i++)
{
    if (args[i] == "--repo" && i + 1 < args.Length)
        repo = args[++i];
    else if (args[i] == "--out" && i + 1 < args.Length)
        wwwroot = args[++i];
}

if (string.IsNullOrEmpty(repo))
{
    var dir = new DirectoryInfo(Directory.GetCurrentDirectory());
    while (dir is not null)
    {
        if (Directory.Exists(Path.Combine(dir.FullName, "sets", "cavedog")))
        {
            repo = dir.FullName;
            break;
        }
        dir = dir.Parent;
    }
    if (string.IsNullOrEmpty(repo))
        repo = Directory.GetCurrentDirectory();
}

if (string.IsNullOrEmpty(wwwroot))
    wwwroot = Path.Combine(repo, "Bestiary.Web", "wwwroot");

Console.WriteLine($"Repo: {repo}");
Console.WriteLine($"Out:  {wwwroot}");

new RepoIndexer(repo, wwwroot).Run();
