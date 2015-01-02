<?php

namespace JDesrosiers\Http;

class AcceptHeader
{
    private $type;
    private $subType;
    private $mediaRange;
    private $q;
    private $extensions;
    private $readOnly = array("type", "subType", "mediaRange", "q", "extensions");

    private $acceptRegex;
    private $extensionsRegex;

    public function __construct($header)
    {
        $separators = '()<>@,;:\\/[\]?={} \t"';
        $token = "[^$separators]+";
        $qValue = '(?:0(?:\.\d{1,3})?|1(?:\.0{1,3})?)';
        $quotedText = '[^"]';
        $quotedPair = '\\.';
        $quotedString = "(?:$quotedText|$quotedPair)*";

        $this->acceptRegex = "/^($token)\/($token)(?:\s*;\s*q\s*=\s*($qValue))?(;.*)?$/";
        $this->extensionsRegex = "/;\s*($token)\s*=\s*(?:($token)|\"($quotedString)\")/";

        $this->parseAccept($header);
    }

    public function __get($name)
    {
        return in_array($name, $this->readOnly) ? $this->$name : null;
    }

    private function parseAccept($header)
    {
        if (preg_match($this->acceptRegex, $header, $matches)) {
            $this->type = $matches[1];
            $this->subType = $matches[2];
            $this->mediaRange = "$this->type/$this->subType";
            $this->q = array_key_exists(3, $matches) && $matches[3] !== "" ? $matches[3] : 1;
            $this->extensions =
                array_key_exists(4, $matches) && $matches[4] !== "" ? $this->parseExtensions($matches[4]) : array();
        } else {
            throw new \DomainException("Invalid accept header");
        }
    }

    private function parseExtensions($extensions)
    {
        if (preg_match_all($this->extensionsRegex, $extensions, $matches)) {
            array_shift($matches);
            $keys = array_shift($matches);
            return $this->combineOr($keys, $matches);
        } else {
            throw new \DomainException("Invalid accept extension");
        }
    }

    private function combineOr($keys, $matches)
    {
        $result = array();
        foreach ($keys as $ndx => $value) {
            $result[$value] = current(array_filter(array_column($matches, $ndx)));
        }

        return $result;
    }
}
