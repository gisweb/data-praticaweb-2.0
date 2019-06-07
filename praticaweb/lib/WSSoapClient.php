<?php

/**
 * Copyright (c) 2007, Roger Veciana
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * Redistributions of source code must retain the above copyright notice, this
 * list of conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation
 * and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

/**
 * This class can add WSSecurity authentication support to SOAP clients
 * implemented with the PHP 5 SOAP extension.
 *
 * It extends the PHP 5 SOAP client support to add the necessary XML tags to
 * the SOAP client requests in order to authenticate on behalf of a given
 * user with a given password.
 *
 * This class was tested with Axis and WSS4J servers.
 *
 * @author Roger Veciana - http://www.phpclasses.org/browse/author/233806.html
 * @author John Kary <johnkary@gmail.com>
 * @see http://stackoverflow.com/questions/2987907/how-to-implement-ws-security-1-1-in-php5
 */
class WSSoapClient extends \SoapClient
{
    /**
     * WS-Security Username
     * @var string
     */
    private $username;

    /**
     * WS-Security Password
     * @var string
     */
    private $password;

    /**
     * Set WS-Security credentials
     *
     * @param string $username
     * @param string $password
     */
    public function __setUsernameToken($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /** 
     * Overwrites the original method adding the security header. As you can
     * see, if you want to add more headers, the method needs to be modified.
     */
    public function __soapCall($function_name, $arguments, $options=null, $input_headers=null, &$output_headers=null)
    {
        return parent::__soapCall($function_name, $arguments, $options, $this->generateWSSecurityHeader());
    }

    /**
     * Generate password digest
     *
     * Using the password directly may work also, but it's not secure to
     * transmit it without encryption. And anyway, at least with
     * axis+wss4j, the nonce and timestamp are mandatory anyway.
     *
     * @return string   base64 encoded password digest
     */
    private function generatePasswordDigest()
    {
        // Can use rand() to repeat the word if the server is under high load
        $this->nonce = mt_rand();
        $this->timestamp = gmdate('Y-m-d\TH:i:s\Z');

        $packedNonce = pack('H*', $this->nonce);
        $packedTimestamp = pack('a*', $this->timestamp);
        $packedPassword = pack('a*', $this->password);

        $hash = sha1($packedNonce . $packedTimestamp . $packedPassword);
        $packedHash = pack('H*', $hash);

        return base64_encode($packedHash);
    }

    /**
     * Generates WS-Security headers
     *
     * @return \SoapHeader
     */
    private function generateWSSecurityHeader()
    {
        $passwordDigest = $this->generatePasswordDigest();

        $xml = '
<wsse:Security SOAP-ENV:mustUnderstand="1" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
    <wsse:UsernameToken>
        <wsse:Username>' . $this->username . '</wsse:Username>
        <wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordDigest">' . $passwordDigest . '</wsse:Password>
        <wsse:Nonce>' . base64_encode(pack('H*', $this->nonce)) . '</wsse:Nonce>
        <wsu:Created xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">' . $this->timestamp . '</wsu:Created>
    </wsse:UsernameToken>
</wsse:Security>
';

        return new \SoapHeader('http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd',
            'Security',
            new \SoapVar($xml, XSD_ANYXML), 
            true
        );
    }
}