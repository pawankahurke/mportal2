#!/usr/bin/perl -w

#  This writes a series of patterns to try to match.
#  We first report the unmodified phrase, and then
#  split the phrase into sentences and try again.

# subroutine takes an input string and returns
# the url query encoded version of it.

sub encodequery($)
{
    my @list = split //, $_[0];
    my $good = "0123456789.-_";
    foreach $elem (@list)
    {
        unless ((('a' le $elem) && ($elem le 'z')) ||
                (('A' le $elem) && ($elem le 'Z')) ||
                (0 <= index($good,$elem)))
        {
            $elem = sprintf("%%%02X",ord($elem));
        }
    }
    return join '', @list;
}



#  The input string is expected to be a correctly encoded phrase.  It should
#  not contain any weird characters or spaces.
#  We take the input string and split it into words at the percent encoded 
#  character boundaries.  We split the result phrase into a list of words, 
#  which do not contain any spaces.
#  We then decode each percent encoded word.  The resulting phrase is
#  just the join of the word list.

sub decodequery($)
{
    my $phrase = $_[0];
    my @list   = ();
    $phrase =~ s/\+/%20/g;
    $phrase =~ s/(%[0-9a-fA-F]{2})/ $1 /g;
    $phrase =~ s/(\s)+/ /g;
    @list = split ' ', $phrase;

    foreach (@list)
    {
        if (/%([0-9a-fA-F]{2})/)
        {
            $_ = chr(hex($1));
        }
    }
    return join '', @list;
}

# returns true (1) if the argument phrase ends 
# a period, exclamation mark, question mark or colon.

sub complete($)
{
    my $phrase = $_[0];
    my $char = chop($phrase);
    if ($char =~ m/[.!?:]/)
    {
        $result = 1;
    }
    else
    {
        $result = 0;
    }
    return $result;
}


# Normally we read the search phrase from the command line.
# We expect this phrase to be already url-encoded, at least
# enough so that unix treats it as a single word.
#
# If there's no command line arguments then run in interactive
# mode and let the user enter the search phrase.

$n = @ARGV;
if ($n < 1)
{
    print "Enter phrase:";
    $pattern = <STDIN>;
    chomp($pattern);
}
else
{
    # We assume that command line percent escapes are
    # correctly formed. i.e. nothing like "%-@" 

    $pattern = decodequery($ARGV[0]);
}

if (length($pattern) == 0)
{
    die "pattern is empty\n";
}


#
#  We use a NUL character as a place marker, to indicate the position 
#  of the delimiter where we always split the phrase.
#

$phrase  = $pattern;
$phrase =~ s/('.*?')/\000/g;  # replace all single quoted strings with nul
$phrase =~ s/(".*?")/\000/g;  # replace all double quoted strings with nul
$phrase =~ s/(\s)+/ /g;       # collapse whitespace.

@list = split ' ', $phrase;
foreach (@list)
{
    if (m/\\/)
    {
        # print "msdos filename: $_\n";
        # if the word contains a backslash we assume 
        # it is a filename and throw it away.
        $_ = "\000";
        next;
    }
    if (m^http(s?)://^)
    {
        # print "url: $_\n";
        # if the word contains http:// or https://
        # then assume this is a url and throw
        # it away.

        $_ = "\000";
        next;
    }
    if (/^[A-Za-z]:/)
    {
        # print "msdos path: $_\n";
        # if the word begins with a letter followed
        # by a colon then assume that it
        # is a drive letter and throw it out.

        $_ = "\000";
        next;
    }
    if (m#/[\w\.]+/#)
    {
        # print "unix path: $_\n";
        # if the word contains alphanumeric
        # surounded by slashes, assume unix
        # path and throw it out.

        $_ = "\000";
        next;
    }
    # print "simple word: $_\n";
}

$phrase = join ' ', @list;                # rebuild search phrase
$phrase =~ s/([.!?:]) /$1\000/g;          # mark sentences
$phrase =~ s/(\s)*\000(,?)(\s)*/\000/g;   # suck up adjacent spaces
$phrase =~ s/(\000)+/\000/g;              # replace multiple nul with single nul

@list     = ();
@sentence = ();

if (length($phrase) > 0)
{
    #print "phrase: $phrase\n";
    @sentence = split /\000/, $phrase;
}

$reduce = $pattern;
$reduce =~ s/(\s)+/ /g;

push @list, encodequery($reduce);

$n = @sentence;

if (3 <= $n)
{
    $m = $n - 1;
    for ($i = 0; $i < $m; $i++)
    {
        $j = $i+1;
        if (complete($sentence[$i]) && complete($sentence[$j]))
        {
            push @list, encodequery($sentence[$i] . " " . $sentence[$j]);
        }
    }
}


if ($n > 1)
{ 
    foreach (@sentence)
    {
        # print "sentence: $_\n";
        if (length($_) > 10)
        {
            push @list, encodequery($_);
        }
    }
}

#  If the splitter didn't split at all then don't
#  push a result unless it is different than the
#  entire string.   Give a try splitting on
#  the conjunctions.

if ($n == 1)
{
    $_ = $sentence[0];
    if ($_ ne $reduce)
    {
        if (length($_) > 10)
        {
            push @list, encodequery($_);
        }
    }

    $phrase = $sentence[0];
    $phrase =~ s/(\s+)and(\s+)/\000/gi;
    $phrase =~ s/(\s+)or(\s+)/\000/gi;
    @sentence = split /\000/, $phrase;
    $n = @sentence;
    if ($n > 1)
    {
        foreach (@sentence)
        {
            if (length($_) > 10)
            {
                push @list, encodequery($_);
            }
        }
    }
}

# print "output\n--------\n";

foreach (@list)
{
    print "$_\n";
}


