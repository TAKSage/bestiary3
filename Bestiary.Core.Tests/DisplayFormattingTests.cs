using Bestiary.Core.Utilities;
using System.Globalization;

namespace Bestiary.Core.Tests;

public sealed class DisplayFormattingTests
{
    [Fact]
    public void ToDisplayTitleCase_Normalizes_Ordinal_After_Digits()
    {
        var en = CultureInfo.GetCultureInfo("en-US");
        Assert.Equal("3rd Party Units", DisplayFormatting.ToDisplayTitleCase("3rd party units", en));
        Assert.Equal("21st After The War", DisplayFormatting.ToDisplayTitleCase("21st after the war", en));
        Assert.Equal("After The War 123", DisplayFormatting.ToDisplayTitleCase("after the war 123", en));
    }
}
