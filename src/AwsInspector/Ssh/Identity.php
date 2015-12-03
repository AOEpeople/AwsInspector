<?php

namespace AwsInspector\Ssh;

use Vault\Vault;

class Identity
{

    protected $privateKeyFile;

    protected $unlocked;

    public function __construct($privateKeyFile)
    {
        if (is_file($privateKeyFile)) {
            $this->privateKeyFile = $privateKeyFile;
        } else {
            $encryptedPrivateKeyFile = $privateKeyFile . '.encrypted';
            $this->privateKeyFile = $privateKeyFile . '.unlocked';
            if (is_file($encryptedPrivateKeyFile)) {
                if (class_exists('\Vault\Vault')) {
                    $vault = new Vault();
                    $vault->decryptFile($encryptedPrivateKeyFile, $this->privateKeyFile);
                    chmod($this->privateKeyFile, 0600);
                    $this->unlocked = true;
                } else {
                    throw new \Exception('Please install aoepeople/vault');
                }
            } else {
                throw new \Exception('Could not find private key file ' . $privateKeyFile);
            }
        }
        $this->privateKeyFile = realpath($this->privateKeyFile);
    }

    public function getPrivateKeyFilePath() {
        return $this->privateKeyFile;
    }

    public function loadIdentity()
    {
        if (!Agent::identityLoaded($this->privateKeyFile)) {
            Agent::addIdentity($this->privateKeyFile);
        }
        return $this;
    }

    public function removeIdentity()
    {
        if (!empty($this->privateKeyFile) && Agent::identityLoaded($this->privateKeyFile)) {
            // echo "Removing identity {$this->unlockedPrivateKeyFile}\n";
            Agent::deleteIdentity($this->privateKeyFile);
            if ($this->unlocked) {
                unlink($this->privateKeyFile);
                $this->unlocked = null;
            }
        }

        return $this;
    }

    public function __destruct()
    {
        $this->removeIdentity();
        // Remove control paths for ssh multiplexing. See \AwsInspector\Ssh->__toString()
        // TODO: deleting these files isn't enough. The mux needs to be closed.
        exec('rm ~/mux* 2> /dev/null');
    }

}