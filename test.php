<?php
/**
 * Minimal Plesk license XML verifier (no Composer).
 * - Verifies ds:Signature (RSA-SHA256) against embedded X.509 certificate
 * - Verifies Reference digest with Enveloped-Signature + Exclusive C14N
 * - Prints key info and expiry
 *
 * NOTE: This is intentionally compact and pragmatic. For production, pin the issuer
 * certificate / fingerprint and add more validation around algorithms & error handling.
 */

// 1) Put your decoded license XML here (the content inside <base64>…</base64>, after base64_decode)
$LICENSE_XML = <<<'XML'
<!-- paste the decoded license XML content here (starting with <plesk-unified:key ...> ) -->
<?xml version="1.0" encoding="UTF-8"?><plesk-unified:key xmlns:plesk-unified="http://parallels.com/schemas/keys/products/plesk/unified/multi" core:format="openfusion-3" xmlns:core="http://parallels.com/schemas/keys/core/3">
   <!--Unique product Key number-->
   <core:key-number core:type="string">PLSK.12271776</core:key-number>
   <!--Key version-->
   <core:key-version core:type="string">0001</core:key-version>
   <!--Key description-->
   <core:description>
      <core:keytype>Web Admin Special Edition (14 Days Trial)</core:keytype>
      <core:property core:value="3">Additional Language Packs</core:property>
   </core:description>
   <!--Product which this license is intended to work on-->
   <core:product core:type="string">plesk-unified</core:product>
   <!--Supported product version-->
   <core:version core:type="string">12.0</core:version>
   <!--Supported product version-->
   <core:version core:type="string">18.0</core:version>
   <!--Supported product version-->
   <core:version core:type="string">17.0</core:version>
   <!--Date after which this license becomes usable (inclusive)-->
   <core:start-date core:type="date">instant</core:start-date>
   <!--Date before which this license is usable (exclusive)-->
   <core:expiration-date core:type="date">2025-11-13</core:expiration-date>
   <!--URL of the service endpoint to use when performing an autoupdate-->
   <core:license-server-url core:type="string">https://ka.plesk.com/xmlrpc</core:license-server-url>
   <!--Date when product will try to perform an autoupdate-->
   <core:update-date core:type="date">never</core:update-date>
   <core:update-ticket core:hidden="true" core:type="string">x4qv9f5yeumfma8siy-5uqtijz5yvrte</core:update-ticket>
   <!--Number of domains-->
   <plesk-unified:domains core:type="integer">3</plesk-unified:domains>
   <!--Number of domain aliases-->
   <plesk-unified:lim_domain_aliases core:type="integer">3</plesk-unified:lim_domain_aliases>
   <!--Number of clients-->
   <plesk-unified:clients core:type="integer">3</plesk-unified:clients>
   <!--Number of webusers-->
   <plesk-unified:webusers core:type="integer">3</plesk-unified:webusers>
   <!--Number of mailnames-->
   <plesk-unified:mailnames core:type="integer">3</plesk-unified:mailnames>
   <!--Number of additional language pack(s)-->
   <plesk-unified:language-packs core:type="integer">3</plesk-unified:language-packs>
   <plesk-unified:mpc-id core:hidden="true" core:type="integer">0</plesk-unified:mpc-id>
   <plesk-unified:mpc-disabled core:hidden="true" core:type="boolean">false</plesk-unified:mpc-disabled>
   <!--Google tools-->
   <plesk-unified:google-tools core:type="boolean">false</plesk-unified:google-tools>
   <!--Number of slaves-->
   <plesk-unified:slaves core:type="integer">50</plesk-unified:slaves>
   <!--EventManager-->
   <plesk-unified:event-manager core:type="boolean">true</plesk-unified:event-manager>
   <!--Domains backup-->
   <plesk-unified:domains-backup core:type="boolean">true</plesk-unified:domains-backup>
   <!--Tomcat support-->
   <plesk-unified:tomcat-support core:type="boolean">true</plesk-unified:tomcat-support>
   <!--Subdomains-->
   <plesk-unified:subdomains-support core:type="boolean">true</plesk-unified:subdomains-support>
   <!--Backward key compatibility restriction-->
   <plesk-unified:backward-restriction core:type="integer">0</plesk-unified:backward-restriction>
   <!--Work Inside Virtuozzo-->
   <plesk-unified:vps-only core:type="boolean">false</plesk-unified:vps-only>
   <!--Work Inside Hyper-V-->
   <plesk-unified:hyper-v core:type="boolean">false</plesk-unified:hyper-v>
   <!--Work Inside VMware-->
   <plesk-unified:vmware core:type="boolean">false</plesk-unified:vmware>
   <!--Work Inside Xen-->
   <plesk-unified:xen core:type="boolean">false</plesk-unified:xen>
   <!--Work Inside KVM-->
   <plesk-unified:kvm core:type="boolean">false</plesk-unified:kvm>
   <!--Work Inside Parallels Hypervisor-->
   <plesk-unified:hypervisor core:type="boolean">false</plesk-unified:hypervisor>
   <!--Work Inside LXC-->
   <plesk-unified:lxc core:type="boolean">false</plesk-unified:lxc>
   <!--Work Inside Parallels Server-->
   <plesk-unified:parallels-server core:type="boolean">false</plesk-unified:parallels-server>
   <!--Global changes-->
   <plesk-unified:global-changes core:type="boolean">true</plesk-unified:global-changes>
   <!--Shell access-->
   <plesk-unified:shell-access core:type="boolean">true</plesk-unified:shell-access>
   <!--Detailed traffic-->
   <plesk-unified:detailed-traffic core:type="boolean">true</plesk-unified:detailed-traffic>
   <!--Notification manager-->
   <plesk-unified:notification-manager core:type="boolean">true</plesk-unified:notification-manager>
   <!--Action log manager-->
   <plesk-unified:action-manager core:type="boolean">true</plesk-unified:action-manager>
   <!--Clients and Domains Expirations management-->
   <plesk-unified:expirations-manager core:type="boolean">true</plesk-unified:expirations-manager>
   <!--Client templates-->
   <plesk-unified:client-templates core:type="boolean">true</plesk-unified:client-templates>
   <!--Ability to use PostgreSQL-->
   <plesk-unified:postgresql-support core:type="boolean">true</plesk-unified:postgresql-support>
   <!--Ability to use ColdFusion-->
   <plesk-unified:coldfusion-support core:type="boolean">true</plesk-unified:coldfusion-support>
   <plesk-unified:ask-update core:hidden="true" core:type="boolean">false</plesk-unified:ask-update>
   <plesk-unified:autoinstaller-config core:hidden="true" core:type="string">true</plesk-unified:autoinstaller-config>
   <!--Ability to use DrWeb-->
   <plesk-unified:drweb-support core:type="boolean">true</plesk-unified:drweb-support>
   <plesk-unified:store-id core:hidden="true" core:type="integer">1</plesk-unified:store-id>
   <!--Ability to use Migration Manager-->
   <plesk-unified:migration-manager core:type="boolean">true</plesk-unified:migration-manager>
   <!--Ability to use MS SQL-->
   <plesk-unified:mssql core:type="boolean">true</plesk-unified:mssql>
   <!--Allowed locales-->
   <plesk-unified:allowed-locales core:type="string">any</plesk-unified:allowed-locales>
   <!--Parallels Plesk Billing accounts count-->
   <plesk-unified:modernbill-accounts core:type="integer">1000</plesk-unified:modernbill-accounts>
   <!--Number of sites-->
   <plesk-unified:sitebuilder-sites core:type="integer">3</plesk-unified:sitebuilder-sites>
   <!--Enable Parallels Plesk Subscriptions Management-->
   <plesk-unified:can-manage-subscriptions core:type="boolean">true</plesk-unified:can-manage-subscriptions>
   <!--Enable Parallels Plesk Service Provider Mode-->
   <plesk-unified:can-manage-accounts core:type="boolean">true</plesk-unified:can-manage-accounts>
   <!--Enable Parallels Plesk Customer Management-->
   <plesk-unified:can-manage-customers core:type="boolean">true</plesk-unified:can-manage-customers>
   <!--Enable Parallels Plesk Resellers Management-->
   <plesk-unified:can-manage-resellers core:type="boolean">true</plesk-unified:can-manage-resellers>
   <!--Enable Custom View Management-->
   <plesk-unified:can-manage-custom-view core:type="boolean">true</plesk-unified:can-manage-custom-view>
   <!--Enable Parallels Plesk Ultimate Wordpress feature-->
   <plesk-unified:wordpress-toolkit core:type="boolean">true</plesk-unified:wordpress-toolkit>
   <!--Enable Parallels Plesk Outgoing Anti-spam feature-->
   <plesk-unified:outgoing-antispam core:type="boolean">true</plesk-unified:outgoing-antispam>
   <!--Enable Parallels Plesk Security Core feature-->
   <plesk-unified:security-core core:type="boolean">true</plesk-unified:security-core>
   <!--Enable Control Suite for Windows by Perigon feature-->
   <plesk-unified:control-suite core:type="boolean">false</plesk-unified:control-suite>
   <!--Enable ServerShield by CloudFlare feature-->
   <plesk-unified:server-shield-cloudflare core:type="boolean">true</plesk-unified:server-shield-cloudflare>
   <!--Enable ServerShield Plus by CloudFlare feature-->
   <plesk-unified:server-shield-plus-cloudflare core:type="boolean">false</plesk-unified:server-shield-plus-cloudflare>
   <!--Web Presence Builder for Plesk - Multi-language Sites Support feature-->
   <plesk-unified:wpb-multi-language-sites-support core:type="boolean">false</plesk-unified:wpb-multi-language-sites-support>
   <!--Remote SmarterMail Support-->
   <plesk-unified:remote-smartermail-enabled core:type="boolean">true</plesk-unified:remote-smartermail-enabled>
   <!--Limit System Resources per Subscription-->
   <plesk-unified:system-resources-limits-enabled core:type="boolean">true</plesk-unified:system-resources-limits-enabled>
   <!--Plesk Branding-->
   <plesk-unified:edition core:type="string">web-admin</plesk-unified:edition>
   <!--Plesk Branding-->
   <plesk-unified:edition-name core:type="string">web admin se edition</plesk-unified:edition-name>
   <!--License type-->
   <plesk-unified:license-type core:type="string">trial</plesk-unified:license-type>
   <!--Information about feature packs-->
   <plesk-unified:feature-packs-info core:encoding="base64" core:type="binary">W3siY29kZSI6Im9mZmVyLWNncm91cHMiLCJrZXlOdW1iZXIiOm51bGwsImRlc2NyaXB0aW9uIjoiUGxlc2sgQ2dyb3VwcyBNYW5hZ2VyIiwib3JpZ2luIjoiYnVuZGxlZCIsImJ1bmRsZSI6IldlYiBBZG1pbiBTcGVjaWFsIEVkaXRpb24gKDE0IERheXMgVHJpYWwpIiwib3JkZXJBY3Rpb24iOm51bGwsInNvdXJjZSI6InRyaWFsIn0seyJjb2RlIjoib2ZmZXItbGFuZ3VhZ2UtcGFjayIsImtleU51bWJlciI6bnVsbCwiZGVzY3JpcHRpb24iOiJMYW5ndWFnZSBQYWNrIiwib3JpZ2luIjoiYnVuZGxlZCIsImJ1bmRsZSI6IldlYiBBZG1pbiBTcGVjaWFsIEVkaXRpb24gKDE0IERheXMgVHJpYWwpIiwib3JkZXJBY3Rpb24iOiJidXkiLCJzb3VyY2UiOiJ0cmlhbCJ9LHsiY29kZSI6Im9mZmVyLXdlYi1wcmVzZW5jZS1idWlsZGVyIiwia2V5TnVtYmVyIjpudWxsLCJkZXNjcmlwdGlvbiI6IldlYiBQcmVzZW5jZSBCdWlsZGVyIiwib3JpZ2luIjoiYnVuZGxlZCIsImJ1bmRsZSI6IldlYiBBZG1pbiBTcGVjaWFsIEVkaXRpb24gKDE0IERheXMgVHJpYWwpIiwib3JkZXJBY3Rpb24iOiJidXkiLCJzb3VyY2UiOiJ0cmlhbCJ9LHsiY29kZSI6IndwLXRvb2xraXQiLCJrZXlOdW1iZXIiOm51bGwsImRlc2NyaXB0aW9uIjoiV1AgVG9vbGtpdCIsIm9yaWdpbiI6ImJ1bmRsZWQiLCJidW5kbGUiOiJXZWIgQWRtaW4gU3BlY2lhbCBFZGl0aW9uICgxNCBEYXlzIFRyaWFsKSIsIm9yZGVyQWN0aW9uIjpudWxsLCJzb3VyY2UiOiJ0cmlhbCJ9XQ==</plesk-unified:feature-packs-info>
   <!--Preferred View-->
   <plesk-unified:preferred-view core:type="string">power user</plesk-unified:preferred-view>
   <!--Information about machineId-->
   <plesk-unified:machine-id-info core:encoding="base64" core:type="binary">W3sidmVyc2lvbiI6MSwidmFsdWUiOiI2RTQ2NzNEQjRBRDc3MzExQUNGQjcwMDJGMEYxMjk0OUI3NzUxNzM4NUM5QjY3NkEyOUREQTE0MEREMzgxOTc5In1d</plesk-unified:machine-id-info>
   <!--Shows if Plesk should check discrepancies between server and license machine id-->
   <plesk-unified:check-machine-id-enabled core:type="boolean">false</plesk-unified:check-machine-id-enabled>
   <ds:Signature xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
      <ds:SignedInfo>
         <ds:CanonicalizationMethod Algorithm="http://parallels.com/schemas/keys/core/3#canonicalize"/>
         <ds:SignatureMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"/>
         <ds:Reference URI="">
            <ds:Transforms>
               <ds:Transform Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315#WithComments"/>
               <ds:Transform Algorithm="http://parallels.com/schemas/keys/core/3#transform"/>
            </ds:Transforms>
            <ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/>
            <ds:DigestValue>wwt8vZpTDJSjiTpXmdGfM6S3I7UxA0OjCvL3uB83Utc=</ds:DigestValue>
         </ds:Reference>
      </ds:SignedInfo>
      <ds:SignatureValue>BUoCpbXxuIAfIrNiKggf3wfyzODZs4Tbq0yjR+4lgCERS1UaZRKxmZLWsW5Rd6vWv3d9i4JCEdV/Wwo4Tp1Agff91/jjTlJ2pJKdD9qB+aK4dD7wpwMgj7LWZ4WBou+ib9Z9YzCPCzD/1EKIDZpqI6/jO2GoZHxKN8S3JA8Lno8p/dWbnCxt1oFFoDvREgVm9bNXarws16VpOZpCMo6VMA30x0P7B+/r1LT3ntqatOb5QnUQxvWpBXoBEDaekwkmdQvZwJWGT9FPH6LFNkGqrjd+YonEC5q6LQucYGw8/7Q0G5/y+beEZCREhICgHPc9yi+PXr5LxgdP2V6EQfNAhg==</ds:SignatureValue>
      <ds:KeyInfo>
         <ds:X509Data>
            <ds:X509Certificate>MIIEnjCCAoYCATAwDQYJKoZIhvcNAQEFBQAwgaYxCzAJBgNVBAYTAkJNMQswCQYDVQQIEwJITTERMA8GA1UEBxMISGFtaWx0b24xHDAaBgNVBAoTE1NXc29mdCBIb2xkaW5ncyBMdGQxHTAbBgNVBAsTFEludGVybmFsIERldmVsb3BtZW50MRwwGgYDVQQDExNLQSByb290IGNlcnRpZmljYXRlMRwwGgYJKoZIhvcNAQkBFg1rYUBzd3NvZnQuY29tMB4XDTEzMTExMTA5MjgxMloXDTQxMDMyOTA5MjgxMlowgYIxFjAUBgNVBAMTDXBsZXNrLXVuaWZpZWQxHTAbBgNVBAsTFEludGVybmFsIERldmVsb3BtZW50MRwwGgYDVQQKExNTV3NvZnQgSG9sZGluZ3MgTHRkMREwDwYDVQQHEwhIYW1pbHRvbjELMAkGA1UECBMCSE0xCzAJBgNVBAYTAkJNMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAjNtqGgtkC6EnyE3Qla2E4+jiQPD2Gw0BhhEbxs5ca2Gr2ue9Y6GTVf1Ci5rri39Pg7/UyDI8HpGB5wCVg36aMugvAT1+hPSnAbDjSxjfZ9zZ5WPMc5t/z9TnFFdHm+ZThngHF449dnIdxbmBzZoIcKQ4VgNtbpI48hAAZSqod6IhUVVnsiOiVKoAvFmOSJWfE1Yo6ENCQoAGHMiICNZiJ8FG3n0p+agKGqblINwC91N42MGsxVpLFGlu3wQLJ/MYwt/2qBXYpVmlhx3cXXWfd6tYbr1HY404FULjjJHdCg47YRQe9bwD595qE+XphkJOPapOLr5F0OIRDPT2uga2MQIDAQABMA0GCSqGSIb3DQEBBQUAA4ICAQBLiGM4TnQ/oYVBniQNcexWxI3EXFhJ41cMeqkx2nYQ5GSCZhdIAAJuPbZCJhqzT+Lln04sgeachA6zn2Vlg9X6Bfx7GVnoSZq5CDmGqj00W+kQ6aF839JoP9WdYWjw1VRN5iVJ8P9oPVtlWxKxu6U3iF3coy8IgEaMa154YUBxvjb5hSrfqjxCUtjrM1w8nJhuxvrawEcva9129fnWR2cNUEVZbiw32MHn2Sh5HEkXgAxRVPvq+2trYkPZMWR7Qz1I5I9i8vmTJxtUuMa6LkmecaHyQpKXSlc4fZ0HEoxiuBYV9nTadePXzkOF+LzjT7igwka3PKDmGY/gyhprK4OgNYn5cRda6NJ9deTuUGKOZZqWA5YO/TPdVqftrFtqrc2Ked/CVepMp3jyyyu6eTlYGBY/Va6xbMDENdM8Son2cyCqwsIxPTufmTvGtlsk6kYq0spAfE4GwCBqIiYME40RR06lgywKTdBi7cHzk6hT6je1TcbAO5pRdhtrYzHSFFi8mJulhVz2Om7iALygneIt2ek//ny/Ke5vy0qGPXFgAi56IGdzOfO4LN3qVNp54aY3k3FaEs8mbkIi/35VQAl6Di5LtcC3VPUapvlcTEu2nXY7Su9kmGm171ripm17s464uWXMPrGr1jHBYHkd7aItrmkPOKqd1iMwqafHSPwj2A==</ds:X509Certificate>
         </ds:X509Data>
      </ds:KeyInfo>
   </ds:Signature>
</plesk-unified:key>
XML;

// ------------------------ Helpers ------------------------

function fail($msg) {
    fwrite(STDERR, "[!] $msg\n");
    exit(1);
}

// Remove all ds:Signature nodes (enveloped-signature transform)
function removeSignatureNodes(DOMNode $context, DOMXPath $xp): void {
    foreach ($xp->query('.//ds:Signature', $context) as $sig) {
        $sig->parentNode->removeChild($sig);
    }
}

/**
 * Canonicalize a node according to Exclusive XML Canonicalization (with or without comments).
 * We infer exclusivity from the algorithm URL when possible; default to exclusive true as Plesk uses it.
 */
function canonicalizeNode(DOMNode $node, bool $exclusive = true, bool $withComments = false): string {
    // DOMNode::C14N(exclusive, withComments)
    return $node->C14N($exclusive, $withComments);
}

/** Base64 -> clean (remove whitespace/newlines) */
function b64_clean(string $s): string {
    return preg_replace('~\s+~', '', $s);
}

/** Get first node helper */
function firstNode(DOMNodeList $nl): ?DOMNode {
    return $nl->length ? $nl->item(0) : null;
}

// ------------------------ Parse & Verify ------------------------

$dom = new DOMDocument();
$dom->preserveWhiteSpace = false;
$dom->formatOutput = false;
if (!$dom->loadXML($LICENSE_XML, LIBXML_NOBLANKS | LIBXML_NOCDATA)) {
    fail('Could not parse license XML');
}

$xp = new DOMXPath($dom);
$xp->registerNamespace('ds',   'http://www.w3.org/2000/09/xmldsig#');
$xp->registerNamespace('core', 'http://parallels.com/schemas/keys/core/3');
$xp->registerNamespace('plus', 'http://parallels.com/schemas/keys/products/plesk/unified/multi'); // a.k.a. plesk-unified

// Locate Signature
$sig = firstNode($xp->query('//ds:Signature'));
if (!$sig) fail('No ds:Signature found');

// Extract SignedInfo, SignatureValue, Method, Cert
$signedInfo = firstNode($xp->query('./ds:SignedInfo', $sig));
$signatureValueNode = firstNode($xp->query('./ds:SignatureValue', $sig));
$signatureMethodNode = firstNode($xp->query('./ds:SignedInfo/ds:SignatureMethod', $sig));
$certNode = firstNode($xp->query('./ds:KeyInfo/ds:X509Data/ds:X509Certificate', $sig));

if (!$signedInfo || !$signatureValueNode || !$signatureMethodNode || !$certNode) {
    fail('Signature structure incomplete');
}

$signatureMethod = $signatureMethodNode->getAttribute('Algorithm');
if (stripos($signatureMethod, 'rsa-sha256') === false) {
    fail("Unsupported SignatureMethod: $signatureMethod (expected RSA-SHA256)");
}

// Canonicalize SignedInfo according to its CanonicalizationMethod
$canonMethodNode = firstNode($xp->query('./ds:SignedInfo/ds:CanonicalizationMethod', $sig));
$canonAlg = $canonMethodNode?->getAttribute('Algorithm') ?? 'http://www.w3.org/2001/10/xml-exc-c14n#';
$exclusive = (stripos($canonAlg, 'xml-exc-c14n') !== false);
$signedInfoC14N = canonicalizeNode($signedInfo, $exclusive, /*withComments*/ false);

// Verify Reference digest (assume one Reference to whole document with enveloped-signature + c14n)
$ref = firstNode($xp->query('./ds:SignedInfo/ds:Reference', $sig));
if (!$ref) fail('No Reference in SignedInfo');

$digestMethodNode = firstNode($xp->query('./ds:DigestMethod', $ref));
$digestValueNode  = firstNode($xp->query('./ds:DigestValue', $ref));

if (!$digestMethodNode || !$digestValueNode) fail('Digest elements missing');
$digestAlg = $digestMethodNode->getAttribute('Algorithm');
if (stripos($digestAlg, 'sha256') === false) fail("Unsupported DigestMethod: $digestAlg (expected SHA-256)");
$expectedDigest = b64_clean($digestValueNode->textContent);

// Build the referenced node set
// Most licenses sign the entire document element ("URI" empty or "#<id>").
// We handle URI="" (root) and URI="#id" if present.
$uri = $ref->getAttribute('URI');
$targetNode = null;
if ($uri === '' || $uri === '#') {
    $targetNode = $dom->documentElement;
} elseif (str_starts_with($uri, '#')) {
    $id = substr($uri, 1);
    // Try xml:id or any attribute with that value
    $targetNode = firstNode($xp->query("//*[@Id='$id' or @ID='$id' or @id='$id']"));
} else {
    fail("Unsupported Reference URI: $uri");
}
if (!$targetNode) fail('Could not resolve Reference target node');

// Clone target subtree and apply transforms: Enveloped-Signature then Exclusive C14N
$clone = $targetNode->cloneNode(true);
$cloneDoc = new DOMDocument();
$cloneDoc->appendChild($cloneDoc->importNode($clone, true));
$cloneXp = new DOMXPath($cloneDoc);
$cloneXp->registerNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');

// Enveloped-signature transform: remove all ds:Signature under target
removeSignatureNodes($cloneDoc->documentElement, $cloneXp);

// For Reference C14N, check declared transforms; default to exclusive c14n
$refTransforms = $xp->query('./ds:Transforms/ds:Transform', $ref);
$refExclusive = true;
foreach ($refTransforms as $t) {
    $alg = $t->getAttribute('Algorithm');
    if (stripos($alg, 'enveloped-signature') !== false) {
        // already removed above
        continue;
    }
    if (stripos($alg, 'xml-exc-c14n') !== false) {
        $refExclusive = true;
    } elseif (stripos($alg, 'xml-c14n') !== false) {
        $refExclusive = false;
    }
}
// Canonicalize referenced node
$refC14N = canonicalizeNode($cloneDoc->documentElement, $refExclusive, /*withComments*/ false);

// Compute digest
$actualDigest = base64_encode(hash('sha256', $refC14N, true));
if (!hash_equals($expectedDigest, $actualDigest)) {
    fail("Digest mismatch (Reference invalid)");
}

// Verify SignatureValue over SignedInfo
$signatureValue = base64_decode(b64_clean($signatureValueNode->textContent), true);
if ($signatureValue === false) fail('SignatureValue base64 decode failed');

$certPem = "-----BEGIN CERTIFICATE-----\n" .
           chunk_split(trim($certNode->textContent), 64, "\n") .
           "-----END CERTIFICATE-----\n";

$pub = openssl_pkey_get_public($certPem);
if ($pub === false) fail('Could not load public key from certificate');

// OPENSSL_ALGO_SHA256 covers rsa-sha256
$ok = openssl_verify($signedInfoC14N, $signatureValue, $pub, OPENSSL_ALGO_SHA256);
if ($ok !== 1) fail('Signature verification failed');

echo "[OK] Signature verified (RSA-SHA256) and Reference digest valid.\n";

// ------------------------ Read useful fields ------------------------

$keyNumber = firstNode($xp->query('//core:key-number'))?->textContent ?? 'n/a';
$keyVersion = firstNode($xp->query('//core:key-version'))?->textContent ?? 'n/a';
$product = firstNode($xp->query('//core:product'))?->textContent ?? 'n/a';
$version = firstNode($xp->query('(//core:version)[1]'))?->textContent ?? 'n/a';
$expNode = firstNode($xp->query('//core:expiration-date'));
$expires = $expNode ? trim($expNode->textContent) : 'n/a';

// Expiry check (treat date as inclusive, UTC)
if ($expires !== 'n/a') {
    $today = new DateTime('today', new DateTimeZone('UTC'));
    $expDate = DateTime::createFromFormat('Y-m-d', $expires, new DateTimeZone('UTC'));
    if ($expDate && $today > $expDate) {
        echo "[WARN] License expired on {$expDate->format('Y-m-d')} (UTC)\n";
    }
}

echo "Key Number : $keyNumber\n";
echo "Key Version: $keyVersion\n";
echo "Product    : $product\n";
echo "Version    : $version\n";
echo "Expires    : $expires\n";

// (Optional) Show some limits (domains, clients, etc.)
$limits = [
    'domains'       => '//plus:domains',
    'clients'       => '//plus:clients',
    'webusers'      => '//plus:webusers',
    'mailnames'     => '//plus:mailnames',
    'language-packs'=> '//plus:language-packs',
];
foreach ($limits as $label => $xpath) {
    $val = firstNode($xp->query($xpath))?->textContent;
    if ($val !== null) {
        echo sprintf("Limit %-12s: %s\n", "($label)", trim($val));
    }
}
