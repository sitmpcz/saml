<?php
declare(strict_types=1);

namespace Sitmp\Saml\Bridges\Tracy;

//use Sitmp\Saml\SamlProvider;
use Tracy;

// tracy panel
// bohuzel se mi nedari ho automaticky zaregistrovat v SamlExtension (ale mozna je to i bezpecnejsi)
// tam kde ho potrebuju, se da zapnout pomoci:
// nejdriv si pomoci DI nacist public \Sitmp\Saml\SamlProvider$samlProvider;
// a pak:
// \Tracy\Debugger::getBar()->addPanel(new \Sitmp\Saml\Bridges\Tracy\SamlPanel($this->samlProvider->getSettingsInfo()));
class SamlPanel implements Tracy\IBarPanel
{
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

	/**
	 * Returns HTML code for custom tab. (Tracy\IBarPanel)
	 */
	public function getTab(): string
	{
        // ikona v SVG se da sehnat treba na https://heroicons.com/
		return '<span title="SAML">
            <svg viewBox="0 0 2048 2048" style="vertical-align: bottom; width:1.23em; height:1.55em">
                <path style="fill:#ff8080;stroke-width:5.83071"
                d="M 927.36507 50.38637 C 820.58984 50.38637 733.13012 74.438044 664.98347 122.54156 C 597.20124 170.28066 563.30969 232.0492 563.30969 307.84868 C 563.30969 364.33387 578.24801 411.70881 608.13049 449.97297 C 638.3774 487.87271 687.75728 517.93775 756.26835 540.16741 C 786.15083 550.00676 818.03706 558.20624 851.92817 564.76581 C 886.18371 570.96096 918.61994 577.70273 949.23127 584.99114 C 978.38492 591.91513 1000.4307 601.93651 1015.3719 615.05565 C 1030.6776 627.81038 1038.3308 643.11582 1038.3308 660.97243 C 1038.3308 680.28672 1033.0468 694.86377 1022.4786 704.70312 C 1012.2748 714.54248 999.15561 722.74195 983.12111 729.30152 C 970.00197 734.40341 953.05833 738.04722 932.28635 740.23375 C 911.51438 742.42027 894.74839 743.51377 881.99367 743.51377 C 832.43247 743.51377 780.6877 734.2208 726.75346 715.63535 C 673.18363 697.0499 624.71333 670.44791 581.34728 635.82796 L 558.38841 635.82796 L 558.38841 830.97515 C 602.11888 849.19618 648.94769 864.31908 698.87331 876.34496 C 749.16335 888.00642 808.01557 893.83724 875.43338 893.83724 C 992.04796 893.83724 1084.246 868.87461 1152.0282 818.94899 C 1220.1749 768.65895 1254.2483 703.42735 1254.2483 623.25482 C 1254.2483 567.13406 1239.31 521.58191 1209.4275 486.59754 C 1179.9095 451.24874 1133.9902 423.3707 1071.6743 402.96315 C 1039.9697 392.75938 1010.2707 384.74156 982.57477 378.91083 C 955.24323 373.0801 926.63573 367.06763 896.75324 360.87248 C 851.20067 351.39754 820.22402 341.01106 803.82509 329.71402 C 787.42617 318.05257 779.22722 301.47196 779.22722 279.97115 C 779.22722 265.75875 784.32936 253.18616 794.53313 242.25354 C 804.73691 230.9565 816.94651 222.57448 831.15891 217.10817 C 847.19342 210.5486 863.77323 206.17549 880.901 203.98896 C 898.39318 201.43802 915.88684 200.16286 933.37902 200.16286 C 982.94022 200.16286 1031.4063 209.09074 1078.7809 226.94735 C 1126.52 244.43954 1166.7871 266.3051 1199.585 292.54339 L 1221.9975 292.54339 L 1221.9975 105.04928 C 1183.7333 89.3792 1138.3641 76.442409 1085.8876 66.238633 C 1033.7754 55.670436 980.9349 50.38637 927.36507 50.38637 z M 1607.9191 65.144695 L 1307.2727 879.07802 L 1518.8195 879.07802 L 1575.1219 714.5423 L 1876.861 714.5423 L 1933.1676 879.07802 L 2150.1778 879.07802 L 1849.5314 65.144695 L 1607.9191 65.144695 z M 2267.1575 65.144695 L 2267.1575 879.07802 L 2465.0374 879.07802 L 2465.0374 334.08709 L 2615.907 687.75781 L 2760.7668 687.75781 L 2911.6363 334.08709 L 2911.6363 879.07802 L 3120.4472 879.07802 L 3120.4472 65.144695 L 2876.6496 65.144695 L 2694.0755 473.47833 L 2510.9552 65.144695 L 2267.1575 65.144695 z M 3328.1697 65.144695 L 3328.1697 879.07802 L 3915.2493 879.07802 L 3915.2493 721.64843 L 3538.0733 721.64843 L 3538.0733 65.144695 L 3328.1697 65.144695 z M 1725.9914 273.4111 L 1826.0262 565.31188 L 1625.9609 565.31188 L 1725.9914 273.4111 z "
                transform="scale(0.45759479,2.1853396)" />
            </svg>
            <span class="tracy-label">SAML</span></span>';
	}


	/**
	 * Returns HTML code for custom panel. (Tracy\IBarPanel)
	 */
	public function getPanel(): ?string
	{
        $retval =  '<h1>SAML konfigurace</h1>

        <div class="tracy-inner tracy-addons-SamlConfigPanel">
            <div class="tracy-inner-container">
                <table>
                    <tbody>';
        foreach ($this->config as $key => $value)
        {
            // mask private key
            if ((is_array($value)) and  (array_key_exists('privateKey', $value))) {
                if (is_string($value['privateKey']) && strlen($value['privateKey'])>200) {
                    $value['privateKey'] = substr($value['privateKey'], 0, 80).'*************** (masked)';
                } else {
                    $value['privateKey'] = '************************ (masked)';
                }
            }
            $retval .= '<tr><th>'. htmlspecialchars((string) $key) .'</th><td>'.Tracy\Dumper::toHtml($value, [Tracy\Dumper::DEPTH => 3]).'</td></tr>';
        }
        $retval .= '</tbody>
                </table>
            </div>
        </div>';
        return $retval;
	}



}
