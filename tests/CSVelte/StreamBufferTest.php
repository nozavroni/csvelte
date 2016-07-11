<?php

use PHPUnit\Framework\TestCase;
use CSVelte\Input\String;
use CSVelte\Stream\EncodeQuotedSpecialCharsWrapper;

/**
 * CSVelte\Input\String Tests
 *
 * @package   CSVelte Unit Tests
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
class StreamBufferTest extends TestCase
{
    protected $CSVstrings = array(
        'NoQuote' => "1,Eldon Base for stackable storage shelf platinum,Muhammed MacIntyre,3,-213.25,38.94,35,Nunavut,Storage & Organization,0.8\n2,1.7 Cubic Foot Compact Office Refrigerators,Barry French,293,457.81,208.16,68.02,Nunavut,Appliances,0.58\n3,Cardinal Slant-DÆ Ring Binder Heavy Gauge Vinyl,Barry French,293,46.71,8.69,2.99,Nunavut,Binders and Binder Accessories,0.39\n4,R380,Clay Rozendal,483,1198.97,195.99,3.99,Nunavut,Telephones and Communication,0.58\n5,Holmes HEPA Air Purifier,Carlos Soltero,515,30.94,21.78,5.94,Nunavut,Appliances,0.5\n6,G.E. Longer-Life Indoor Recessed Floodlight Bulbs,Carlos Soltero,515,4.43,6.64,4.95,Nunavut,Office Furnishings,0.37\n7,Angle-D Binders with Locking Rings Label Holders,Carl Jackson,613,-54.04,7.3,7.72,Nunavut,Binders and Binder Accessories,0.38\n8,SAFCO Mobile Desk Side File Wire Frame,Carl Jackson,613,127.70,42.76,6.22,Nunavut,Storage & Organization,\n9,SAFCO Commercial Wire Shelving Black,Monica Federle,643,-695.26,138.14,35,Nunavut,Storage & Organization,\n10,Xerox 198,Dorothy Badders,678,-226.36,4.98,8.33,Nunavut,Paper,0.38",
        'QuoteMinimal' => "Bank Name,City,ST,CERT,Acquiring Institution,Closing Date,Updated Date\nFirst CornerStone Bank,\"King of\nPrussia\",PA,35312,First-Citizens Bank & Trust Company,6-May-16,25-May-16\nTrust Company Bank,Memphis,TN,9956,The Bank of Fayette County,29-Apr-16,25-May-16\nNorth Milwaukee State Bank,Milwaukee,WI,20364,First-Citizens Bank & Trust Company,11-Mar-16,16-Jun-16\nHometown National Bank,Longview,WA,35156,Twin City Bank,2-Oct-15,13-Apr-16\nThe Bank of Georgia,Peachtree City,GA,35259,Fidelity Bank,2-Oct-15,13-Apr-16\nPremier Bank,Denver,CO,34112,\"United Fidelity \r\n \r \r \n \r\n Bank, fsb\",10-Jul-15,17-Dec-15\nEdgebrook Bank,Chicago,IL,57772,Republic Bank of Chicago,8-May-15,2-Jun-16\nDoral Bank,San Juan,PR,32102,Banco Popular de Puerto Rico,27-Feb-15,13-May-15\nCapitol\t City Bank & Trust: Company,Atlanta,GA,33938,First-Citizens Bank & Trust: Company,13-Feb-15,21-Apr-15\nHighland: Community Bank,Chicago,IL,20290,\"United Fidelity Bank, fsb\",23-Jan-15,21-Apr-15\nFirst National Bank of Crestview ,Crestview,FL,17557,First NBC Bank,16-Jan-15,15-Jan-16\nNorthern Star Bank,Mankato,MN,34983,BankVista,19-Dec-14,6-Jan-16\n\"Frontier Bank, FSB D/B/A El Paseo Bank\",Palm Desert,CA,34738,\"Bank of Southern California, N.A.\",7-Nov-14,6-Jan-16\nThe National Republic Bank of Chicago,Chicago,IL,916,State Bank of Texas,24-Oct-14,6-Jan-16\nNBRS Financial,Rising Sun,MD,4862,Howard Bank,17-Oct-14,26-Mar-15\n\"GreenChoice Bank, fsb\",Chicago,IL,28462,\"Providence Bank, LLC\",25-Jul-14,28-Jul-15\nEastside Commercial Bank,Conyers,GA,58125,Community: Southern Bank,18-Jul-14,28-Jul-15\nThe Freedom State Bank ,Freedom,OK,12483,Alva State Bank & Trust Company,27-Jun-14,25-Mar-16\nValley Bank,Fort Lauderdale,FL,21793,\"Landmark Bank, National Association\",20-Jun-14,29-Jun-15\nValley Bank,Moline,IL,10450,Great Southern Bank,20-Jun-14,26-Jun-15\nSlavie Federal Savings Bank,Bel Air,MD,32368,\"Bay Bank, FSB\",30-May-14,15-Jun-15\nColumbia Savings Bank,Cincinnati,OH,32284,\"United Fidelity Bank, fsb\",23-May-14,28-May-15\nAztecAmerica Bank ,Berwyn,IL,57866,Republic Bank of Chicago,16-May-14,18-Jul-14\nAllendale County Bank,Fairfax,SC,15062,Palmetto State Bank,25-Apr-14,18-Jul-14\nVantage Point Bank,Horsham,PA,58531,First Choice Bank,28-Feb-14,3-Mar-15\n\"Millennium Bank, National\n Association\",Sterling,VA,35096,WashingtonFirst Bank,28-Feb-14,3-Mar-15\nSyringa Bank,Boise,ID,34296,Sunwest Bank,31-Jan-14,12-Apr-16\nThe Bank of Union,El Reno,OK,17967,BancFirst,24-Jan-14,25-Mar-16\nDuPage National Bank,West Chicago,IL,5732,Republic Bank of Chicago,17-Jan-14,19-F\n"
    );

    public function testJustToKillPHPUnitWarning()
    {
        $this->assertTrue(true);
    }

    public function tstStreamWrapperOrBufferOrSomethingToEncodeOrEscapeQuotedSpecialChars()
    {
        // I don't want my Readable classes/objects to have to know anything about
        // the format they're reading. I want it to remain as stupid as possible.
        // It simply reads characters/lines from whatever source it is reading
        // from and returns them. I also don't want my reader(s) to have to do a
        // bunch of crazy convoluted logic to read another line if a line ends on
        // a quoted newline. Because of this, I'm going to need some way of
        // encoding or escaping newlines, delims, etc. before the Readable tries
        // reading but without introducing extra characters and stuff that will
        // throw off the reader (for instance, if it reads 100 chars, and the
        // escape sequence is 5 characters longer than the character it is
        // escaping, that will cause a problem). This is a series of tests meant
        // to ensure all these problems are handled properly.
    }

    public function TurnedOfftestStreamWrapperSolvesThisProblem()
    {
        stream_wrapper_register("csvelte", EncodeQuotedSpecialCharsWrapper::class)
            or die("Failed to register protocol");

        $fp = fopen("csvelte:///Users/luke/localdev/csvelte/tests/files/banklist-qsc.csv", "r+");
        $line_no = 0;
        while (false !== ($line = fgets($fp, 1024))) {
            if ($line_no > 10) break;
            //dd($line, false);
            $line_no++;
        }

//         $read = "Trust Company Bank,Memphis,TN,9956,\"The Bank of
//
//
// Another line of stuff
// And another line
// Fayette County\",29-Apr-16,25-May-16
// North Milwaukee State Bank,Milwaukee,WI,20364,\"First-\nCitizen's \r\nBank & \rTrust Company\",11-Mar-16,16-Jun-16
// Hometown National Bank,Longview,WA,35156,Twin City Bank,2-Oct-15,13-Apr-16
// The Bank of Georgia,Peachtree City,GA,35259,Fidelity Bank,2-Oct-15,13-Apr-16";
//         $rbRepl = [19 => 44, 26 => 44, 28 => 44, 32 => 10, 44 => 13, 41 => 44];
//         $rbPos = 0;
//         $data = $offset = 0;
//         $k = 0;
//         $rbPos += strlen($read);
//         $ofset = 0;
//         foreach ($rbRepl as $pos => $repl) {
//             $encoded = sprintf("<[=%%%03d=]>", $repl);
//             $top = substr($read, 0, $pos + $offset - 1);
//             $bottom = substr($read, strlen($top)+1);
//             $read = $top . $encoded . $bottom;
//             $offset = strlen($encoded) + $offset;
//         }
//         dd($read);
//         return $read;
    }
}
