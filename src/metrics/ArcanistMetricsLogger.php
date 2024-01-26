<?php

final class ArcanistMetricsLogger extends Phobject {

  private static $instance;

  private $eventFile = null;
  private $repositoryName;
  private $osType;
  private $cmdUuid;

  private $nounit;
  private $nolint;
  private $noRebase;
  private $allowUntracked;

  private $author;
  private $revisionID;
  private $diffID;

  private function __construct() {
    $this->eventFile = new TempFile();
    $repository_name;
    exec('basename `git rev-parse --show-toplevel`', $repository_name);
    $this->setRepositoryName(implode(",",$repository_name));
    $this->setOsType(strtolower(php_uname('s')));
    $this->setCmdUuid($this->generateUuid());
    $this->setNounit(false);
    $this->setNolint(false);
    $this->setNorebase(false);
    $this->setAllowUntracked(false);
  }

  public static function getInstance() {
    if (empty(self::$instance)) {
      self::$instance = new ArcanistMetricsLogger();
    }
    return self::$instance;
  }

  private function setOsType($osType) {
    if (empty($this->osType)) {
      $this->osType = $osType;
    }
  }

  private function setRepositoryName($repositoryName) {
    if (empty($this->repositoryName)) {
      $this->repositoryName = $repositoryName;
    }
  }

  private function setCmdUuid($cmdUuid) {
    if (empty($this->cmdUuid)) {
      $this->cmdUuid = $cmdUuid;
    }
  }

  public function setNounit($nounit) {
    $this->nounit = $nounit;
  }

  public function setNolint($nolint) {
    $this->nolint = $nolint;
  }

  public function setNorebase($noRebase) {
    $this->noRebase = $noRebase;
  }

  public function setAllowUntracked($allowUntracked) {
    $this->allowUntracked = $allowUntracked;
  }

  public function setAuthor($author) {
    if (empty($this->author)) {
      $this->author = $author;
    }
  }

  public function setRevisionID($revisionID) {
    if (empty($this->revisionID)) {
      $this->revisionID = $revisionID;
    }
  }

  public function setDiffID($diffID) {
    if (empty($this->diffID)) {
      $this->diffID = $diffID;
    }
  }

  public function getLogFile() {
    return $this->eventFile;
  }

  public function logEvent($data) {
    if (!Filesystem::pathExists($this->eventFile)) {
      $this->eventFile = new TempFile();
    }
    Filesystem::appendFile($this->eventFile, json_encode($data)."\n");
  }

  public function getAllEvents() {
    $metadata = [
      "author" => $this->author,
      "os_type" => $this->osType,
      "repository_name" => $this->repositoryName,
      "cmd_uuid" => $this->cmdUuid,
      "revision_id" => $this->revisionID,
      "diff_id" => $this->diffID,
      "nounit" => $this->nounit,
      "nolint" => $this->nolint,
      "no_rebase" => $this->noRebase,
      "allow-untracked" => $this->allowUntracked,
    ];

    $events = array();
    if (Filesystem::pathExists($this->eventFile)) {
      $contents = trim(Filesystem::readFile($this->eventFile));
      $lines = preg_split("/\n/", $contents);
      foreach ($lines as $line) {
        $events[] = array_merge(json_decode($line, true), $metadata);
      }
    }
    return $events;
  }

  private function generateUuid() {
    // generate 16 bytes (128 bits) of random data.
    $data = random_bytes(16);
    assert(strlen($data) == 16);

    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

    // return 36 character UUID.
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
  }
}
?>
