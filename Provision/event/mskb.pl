#!/usr/bin/perl -w

use LWP::Simple;

$pattern    = "";
$debug      = 0;
$exact      = 1;
$batch      = 1;
$help       = 0;
$test       = 0;

# command line options
#
#   -b  batch mode (default)
#   -i  interactive mode
#   -d  report debuging information
#   -a  search for all words
#   -e  search for exact phrase.
#   -h  show help
#   -t  selftest


foreach (@ARGV)
{
    #print "$_\n";
    if (/^-/)
    {
        if (/d/)
        {
            $debug++;
        }
        if (/a/)
        {
            $exact = 0;
        }
        if (/e/)
        {
            $exact = 1;
        }
        if (/i/)
        {
            $batch = 0;
        }
        if (/b/)
        {
            $batch = 1;
        }
        if (/h/)
        {
            $help = 1;
        }
        if (/t/)
        {
            $batch = 1;
            $test = 1;
            $exact = 0;
            $pattern = "ioctlsocket";
        }
    }
    else
    {
        # We assume that command line percent escapes are
        # correctly formed. i.e. nothing like "%-@" 

        $pattern = $_;
    }
}


if ($help)
{
    print "options\n";
    print "   -b  batch mode (default)\n";
    print "   -i  interactive mode\n";
    print "   -d  debug\n";
    print "   -h  help\n";
    print "   -e  exact phrase search (default)\n";
    print "   -a  all words search\n";
    print "   -t  perform self test\n";
    exit 0;
}


# Normally we read the search phrase from the command line.
# We expect this phrase to be already url-encoded, at least
# enough so that unix treats it as a single word.
#
# If there's no command line arguments then run in interactive
# mode and let the user enter the search phrase.  If the phrase
# contains any percent signs we have to manually replace them
# first.

if (length($pattern) == 0)
{
    if ($batch)
    {
        die "pattern is empty in batch mode\n";
    }
    else
    {
        if ($exact)
        {
            print "Enter exact phrase:";
        }
        else
        {
            print "Enter all words:";
        }
        $pattern = <STDIN>;
        chomp($pattern);
        $pattern =~ s/%/%25/g;
    }
}

@list = split //, $pattern;

$good = "0123456789%.-_";
foreach $elem (@list)
{
    unless ((('a' le $elem) && ($elem le 'z')) ||
            (('A' le $elem) && ($elem le 'Z')) ||
            (0 <= index($good,$elem)))
    {
        $elem = sprintf("%%%02X",ord($elem));
    }
}

$pattern = join '', @list;
$pattern =~ s/%20/+/g;

if (length($pattern) == 0)
{
    die "pattern is empty!\n";
}

if ($exact)
{
    $type = "PHRASE";
}
else
{
    $type = "ALL";
}


@list = ();

# http://search.microsoft.com/default.asp?
#   so=RECCNT&
#   siteid=us&p=1&nq=NEW&
#   qu=ioctlsocket&
#   IntlSearch=&boolean=ALL&ig=3&ig=4&ig=6&i=02&i=03&i=05&btnsearch=Submit+query

#  $url = "http://search.microsoft.com/default.asp?";
#  push @list, $url;
#  push @list, "so=RECCNT&";
#  push @list, "siteid=us&";
#  push @list, "p=1&";
#  push @list, "nq=NEW&";
#  push @list, "qu=$pattern&";
#  push @list, "IntlSearch=&";
#  push @list, "boolean=$type&";
#  push @list, "ig=3&";
#  push @list, "ig=4&";
#  push @list, "ig=6&";
#  push @list, "i=02&";
#  push @list, "i=03&";
#  push @list, "i=05&";
#  push @list, "btnsearch=Submit+query";


# http://search.support.microsoft.com/search/default.aspx?
#   Catalog=LCID%3D1033%26CDID%3DEN-US-KB%26PRODLISTSRC%3DON&
#   Product=&KeywordType=ALL&Titles=false&numDays=&
#   maxResults=25&withinResults=&
#   Queryl=ioctlsocket&
#   Query=ioctlsocket&
#   QuerySource=gsfxSearch_Query

#  $url = "http://search.support.microsoft.com/search/default.aspx?";
#  push @list, $url;
#  push @list, "Catalog=LCID%3D1033%26CDID%3DEN-US-KB%26PRODLISTSRC%3DON&";
#  push @list, "Product=&";
#  push @list, "KeywordType=$type&";
#  push @list, "Titles=false&numDays=&";
#  push @list, "maxResults=25&";
#  push @list, "withinResults=&";
#  push @list, "Queryl=$pattern&";
#  push @list, "Query=$pattern&";
#  push @list, "QuerySource=gsfxSearch_Query";



# 22-Dec-2005
# http://search.support.microsoft.com/search/default.aspx?
#   spid=global&
#   query=ioctlsocket&
#   catalog=LCID%3D1033&
#   pwt=false&
#   title=false&
#   kt=ALL&
#   mdt=0&
#   comm=1&
#   ast=1&
#   ast=2&
#   ast=3&
#   mode=a&
#   x=10&
#   y=9

$url = "http://search.support.microsoft.com/search/default.aspx?";
push @list, $url;
push @list, "spid=global&";
push @list, "query=$pattern&";
push @list, "catalog=LCID%3D1033&";
push @list, "pwt=false&";
push @list, "title=false&";
push @list, "kt=$type&";
push @list, "mdt=0&";
push @list, "comm=1&";
push @list, "ast=1&";
push @list, "ast=2&";
push @list, "ast=3&";
push @list, "mode=a&";
push @list, "x=10&";
push @list, "y=9";


$query   = join '', @list;
@list    = ();
$oname   = "/tmp/mskb.$$";

if ($debug)
{
    print "   exact: $exact\n";
    print "   batch: $batch\n";
    print "    type: $type\n";
    print "     url: $url\n";
    print " pattern: $pattern\n";
    print "   query: $query\n";
    print "   oname: $oname\n";
}

$buffer = get($query);
$errmsg = $!;

unless (defined($buffer))
{
    unless ($debug)
    {
        print "    url: $url\n";
        print "pattern: $pattern\n";
        print "  exact: $exact\n";
        print "  batch: $batch\n";
        print "   type: $type\n";
        print "  query: $query\n";
        print "  oname: $oname\n";
    }
    die "Unable to access Microsoft Knowledge Base, $errmsg\n";
}


if ($debug)
{
    # if somehow the output file is already
    # existing, delete it.

    if ( -f $oname)
    {
        unlink $oname;
    }

    # we can't really debug this script unless
    # we can leave the contents of the buffer
    # someplace where we can look at it.

    unless (open(OFYLE,">$oname"))
    {
        die "can not create $oname\n";
    }

    printf OFYLE ("pattern: %s\n", $pattern);
    printf OFYLE ("query: %s\n", $query);
    printf OFYLE ("result: \n%s\n", $buffer);
    close OFYLE;
}

# $kb is the URL that all knowledge base articles 
# currently start with.  There is *NO* guarantee
# that this will not change.
#
# $sss is substring size; the size of the substring 
# we will examine.  It must be big enough to contain 
# the URL we are looking for, but too small to contain 
# two of them.


$buffer =~ s/%3B/;/g;
# $kb     = 'http://support.microsoft.com/default.aspx?scid=kb;en-us;';
# $xb     = 'http://support\.microsoft\.com/default\.aspx\?scid=kb;en-us;';
# 22-Dec-2005
$kb     = 'http://support.microsoft.com/kb/';
$xb     = 'http://support\.microsoft\.com/kb/';
$len    = length($kb);
$sss    = $len + 20;
@list   = ();
$pos    = 0;

$useThis = 1;
while ($pos >= 0)
{
    $pos = index $buffer, $kb, $pos;
    if ($pos >= 0)
    {
        $_ = substr $buffer, $pos, $sss;
        if (m|$xb\d+|o)
        {
            # 22-Dec-05
            # The page now lists each URL twice, so we skip every other one.
            if ($useThis)
            {
                push @list, $&;
            }
            $useThis = 1 - $useThis;
            $pos = $pos + length($&);
        }
        else
        {
            $pos = $pos + $len; 
        }
    }
}


#  The search result page usually contains this
#  special phrase:
#
#   "1 to 10 of 100 results"
#
#  If we find this phrase, and the reported count
#  makes sense, then use it.

# 22-Dec-2005
#  Unfortunately, Microsoft changed the page.  Now it seems to act like this:
#    1. If there are 20 or fewer results, the header says "Results 1-N".
#    2. If there are more than 20 results, there is a "Next>>" and going
#       to successive pages say "Results 21-40" etc.
#    3. You can never see more than 100 results total.
#  So, for now, we just use the count of results on the first page.

$count = @list;

# Beware the Ides of March. 
#
# selftest results of 3/15/2002
# all words search for "ioctlsocket".
#
# http://support.microsoft.com/support/kb/articles/Q125/4/86.asp
# http://support.microsoft.com/support/kb/articles/Q241/0/04.ASP
# http://support.microsoft.com/support/kb/articles/Q192/5/99.ASP
# http://support.microsoft.com/support/kb/articles/Q181/6/11.ASP
# http://support.microsoft.com/support/kb/articles/Q179/9/42.ASP
# http://support.microsoft.com/support/kb/articles/Q297/1/10.ASP
# http://support.microsoft.com/support/kb/articles/Q303/1/01.ASP
# http://support.microsoft.com/support/kb/articles/Q122/5/44.asp
# http://support.microsoft.com/support/kb/articles/q147/7/14.asp

#
#  same search on 1/10/2003
#
#  http://support.microsoft.com/default.aspx?scid=kb;en-us;125486
#  http://support.microsoft.com/default.aspx?scid=kb;en-us;297110
#  http://support.microsoft.com/default.aspx?scid=kb;en-us;241004
#  http://support.microsoft.com/default.aspx?scid=kb;en-us;181611
#  http://support.microsoft.com/default.aspx?scid=kb;en-us;192599
#  http://support.microsoft.com/default.aspx?scid=kb;en-us;147714
#  http://support.microsoft.com/default.aspx?scid=kb;en-us;179942
#  http://support.microsoft.com/default.aspx?scid=kb;en-us;303101
#  http://support.microsoft.com/default.aspx?scid=kb;en-us;122544
#




if ($test)
{
    # we expect to find at least these nine known matches.

    foreach (@list)
    {
        print "$_\n";
    }

    if ($count < 9)
    {
        print "failure: $count found.\n";
    }
    else
    {
        print "success: $count found.\n";
    }
    $count = 0;
}


if ($count > 0)
{
    $_ = $buffer;
# 22-Dec-2005
#  This is the omission described above; we don't check for the count any more.
#      if (/(\d*)\s*to\s*(\d*)\s*of\s*(\d*)\s*results/)
#      {
#          $n = $3;
#          if ($n > $count)
#          {
#              $count = $n;
#          }
#      }
    print "$count\n";
    print "$query\n";
    foreach (@list)
    {
        print "$_\n";
    }
    @list = ();
}
