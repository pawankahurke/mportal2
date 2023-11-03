<?php

class Filter
{
    private $allowed_protocols = array(), $allowed_tags = array();
    
    public function addAllowedProtocols($protocols)
    {
        $this->allowed_protocols = (array)$protocols;
    }
    
    public function addAllowedTags($tags)
    {
        $this->allowed_tags = (array)$tags;
    }
    
    public function xss($string)
    {
                        if (!$this->isUtf8($string)) {
            return '';
        }
        
                $string = str_replace(chr(0), '', $string);
        
                $string = preg_replace('%&\s*\{[^}]*(\}\s*;?|$)%', '', $string);
        
                $string = str_replace('&', '&amp;', $string);
        
                        $string = preg_replace('/&amp;#([0-9]+;)/', '&#\1', $string);
        
                $string = preg_replace('/&amp;#[Xx]0*((?:[0-9A-Fa-f]{2})+;)/', '&#x\1', $string);
        
                $string = preg_replace('/&amp;([A-Za-z][A-Za-z0-9]*;)/', '&\1', $string);
        
        return preg_replace_callback('%
            (
            <(?=[^a-zA-Z!/])  # a lone <
            |                 # or
            <!--.*?-->        # a comment
            |                 # or
            <[^>]*(>|$)       # a string that starts with a <, up until the > or the end of the string
            |                 # or
            >                 # just a >
            )%x', array($this, 'split'), $string);
    }
    
    private function isUtf8($string)
    {
        if (strlen($string) == 0) {
            return true;
        }
        
        return (preg_match('/^./us', $string) == 1);
    }
    
    private function split($m)
    { 
        $string = $m[1];
        
        if (substr($string, 0, 1) != '<') {
                        return '&gt;';
        } elseif (strlen($string) == 1) {
                        return '&lt;';
        }
        
        if (!preg_match('%^<\s*(/\s*)?([a-zA-Z0-9\-]+)([^>]*)>?|(<!--.*?-->)$%', $string, $matches)) {
                        return '';
        }
        
        $slash = trim($matches[1]);
        $elem = &$matches[2];
        $attrlist = &$matches[3];
        $comment = &$matches[4];
        
        if ($comment) {
            $elem = '!--';
        }
        
        if (!in_array(strtolower($elem), $this->allowed_tags, true)) {
                        return '';
        }
        
        if ($comment) {
            return $comment;
        }
        
        if ($slash != '') {
            return "</$elem>";
        }
        
                $attrlist = preg_replace('%(\s?)/\s*$%', '\1', $attrlist, -1, $count);
        $xhtml_slash = $count ? ' /' : '';
        
                $attr2 = implode(' ', $this->attributes($attrlist));
        $attr2 = preg_replace('/[<>]/', '', $attr2);
        $attr2 = strlen($attr2) ? ' ' . $attr2 : '';
        
        return "<$elem$attr2$xhtml_slash>";
    }
    
    private function attributes($attr) {
        
        $attrarr = array();
        $mode = 0;
        $attrname = '';
        
        while (strlen($attr) != 0) {
                        $working = 0;
            
            switch ($mode) {
                case 0:
                                        if (preg_match('/^([-a-zA-Z]+)/', $attr, $match)) {
                        $attrname = strtolower($match[1]);
                        $skip = ($attrname == 'style' || substr($attrname, 0, 2) == 'on');
                        $working = $mode = 1;
                        $attr = preg_replace('/^[-a-zA-Z]+/', '', $attr);
                    }
                    break;
                case 1:
                                        if (preg_match('/^\s*=\s*/', $attr)) {
                        $working = 1;
                        $mode = 2;
                        $attr = preg_replace('/^\s*=\s*/', '', $attr);
                        break;
                    }
                    
                    if (preg_match('/^\s+/', $attr)) {
                        $working = 1;
                        $mode = 0;
                        
                        if (!$skip) {
                            $attrarr[] = $attrname;
                        }
                        
                        $attr = preg_replace('/^\s+/', '', $attr);
                    }
                    break;
                case 2:
                                        if (preg_match('/^"([^"]*)"(\s+|$)/', $attr, $match)) {
                        $thisval = $this->badProtocol($match[1]);
                        
                        if (!$skip) {
                            $attrarr[] = "$attrname=\"$thisval\"";
                        }
                        
                        $working = 1;
                        $mode = 0;
                        $attr = preg_replace('/^"[^"]*"(\s+|$)/', '', $attr);
                        break;
                    }
                    
                    if (preg_match("/^'([^']*)'(\s+|$)/", $attr, $match)) {
                        $thisval = $this->badProtocol($match[1]);
                        
                        if (!$skip) {
                            $attrarr[] = "$attrname='$thisval'";
                        }
                        
                        $working = 1;
                        $mode = 0;
                        $attr = preg_replace("/^'[^']*'(\s+|$)/", '', $attr);
                        break;
                    }
                    
                    if (preg_match("%^([^\s\"']+)(\s+|$)%", $attr, $match)) {
                        $thisval = $this->badProtocol($match[1]);
                        
                        if (!$skip) {
                            $attrarr[] = "$attrname=\"$thisval\"";
                        }
                        
                        $working = 1;
                        $mode = 0;
                        $attr = preg_replace("%^[^\s\"']+(\s+|$)%", '', $attr);
                    }
                break;
            }
            
            if ($working == 0) {
                                $attr = preg_replace('/
                ^
                (
                "[^"]*("|$)     # - a string that starts with a double quote, up until the next double quote or the end of the string
                |               # or
                \'[^\']*(\'|$)| # - a string that starts with a quote, up until the next quote or the end of the string
                |               # or
                \S              # - a non-whitespace character
                )*              # any number of the above three
                \s*             # any number of whitespaces
                /x', '', $attr);
                
                $mode = 0;
            }
        }
        
                if ($mode == 1 && !$skip) {
            $attrarr[] = $attrname;
        }
        
        return $attrarr;
    }
    
    private function badProtocol($string) {
        
        $string = html_entity_decode($string, ENT_QUOTES, 'UTF-8');
        return htmlspecialchars($this->stripDangerousProtocols($string), ENT_QUOTES, 'UTF-8');
    }
    
    private function stripDangerousProtocols($uri)
    {
        
                do {
            $before = $uri;
            $colonpos = strpos($uri, ':');
            
            if ($colonpos > 0) {
                                $protocol = substr($uri, 0, $colonpos);
                
                                                                if (preg_match('![/?#]!', $protocol)) {
                    break;
                }
                
                                                if (!in_array(strtolower($protocol), $this->allowed_protocols, true)) {
                    $uri = substr($uri, $colonpos + 1);
                }
            }
        } while ($before != $uri);
        
        return $uri;
    } 
}
?>