<?php

use PHPUnit\Framework\TestCase;
use CSVelte\Input\String;

/**
 * CSVelte\Input\String Tests
 *
 * @package   CSVelte Unit Tests
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
class InputStringTest extends TestCase
{
    protected $CSVstrings = array(
        'NoQuote' => "1,Eldon Base for stackable storage shelf platinum,Muhammed MacIntyre,3,-213.25,38.94,35,Nunavut,Storage & Organization,0.8\n2,1.7 Cubic Foot Compact Office Refrigerators,Barry French,293,457.81,208.16,68.02,Nunavut,Appliances,0.58\n3,Cardinal Slant-DÆ Ring Binder Heavy Gauge Vinyl,Barry French,293,46.71,8.69,2.99,Nunavut,Binders and Binder Accessories,0.39\n4,R380,Clay Rozendal,483,1198.97,195.99,3.99,Nunavut,Telephones and Communication,0.58\n5,Holmes HEPA Air Purifier,Carlos Soltero,515,30.94,21.78,5.94,Nunavut,Appliances,0.5\n6,G.E. Longer-Life Indoor Recessed Floodlight Bulbs,Carlos Soltero,515,4.43,6.64,4.95,Nunavut,Office Furnishings,0.37\n7,Angle-D Binders with Locking Rings Label Holders,Carl Jackson,613,-54.04,7.3,7.72,Nunavut,Binders and Binder Accessories,0.38\n8,SAFCO Mobile Desk Side File Wire Frame,Carl Jackson,613,127.70,42.76,6.22,Nunavut,Storage & Organization,\n9,SAFCO Commercial Wire Shelving Black,Monica Federle,643,-695.26,138.14,35,Nunavut,Storage & Organization,\n10,Xerox 198,Dorothy Badders,678,-226.36,4.98,8.33,Nunavut,Paper,0.38",
        'QuoteMinimal' => "Bank Name,City,ST,CERT,Acquiring Institution,Closing Date,Updated Date\nFirst CornerStone Bank,\"King of\nPrussia\",PA,35312,First-Citizens Bank & Trust Company,6-May-16,25-May-16\nTrust Company Bank,Memphis,TN,9956,The Bank of Fayette County,29-Apr-16,25-May-16\nNorth Milwaukee State Bank,Milwaukee,WI,20364,First-Citizens Bank & Trust Company,11-Mar-16,16-Jun-16\nHometown National Bank,Longview,WA,35156,Twin City Bank,2-Oct-15,13-Apr-16\nThe Bank of Georgia,Peachtree City,GA,35259,Fidelity Bank,2-Oct-15,13-Apr-16\nPremier Bank,Denver,CO,34112,\"United Fidelity \r\n \r \r \n \r\n Bank, fsb\",10-Jul-15,17-Dec-15\nEdgebrook Bank,Chicago,IL,57772,Republic Bank of Chicago,8-May-15,2-Jun-16\nDoral Bank,San Juan,PR,32102,Banco Popular de Puerto Rico,27-Feb-15,13-May-15\nCapitol\t City Bank & Trust: Company,Atlanta,GA,33938,First-Citizens Bank & Trust: Company,13-Feb-15,21-Apr-15\nHighland: Community Bank,Chicago,IL,20290,\"United Fidelity Bank, fsb\",23-Jan-15,21-Apr-15\nFirst National Bank of Crestview ,Crestview,FL,17557,First NBC Bank,16-Jan-15,15-Jan-16\nNorthern Star Bank,Mankato,MN,34983,BankVista,19-Dec-14,6-Jan-16\n\"Frontier Bank, FSB D/B/A El Paseo Bank\",Palm Desert,CA,34738,\"Bank of Southern California, N.A.\",7-Nov-14,6-Jan-16\nThe National Republic Bank of Chicago,Chicago,IL,916,State Bank of Texas,24-Oct-14,6-Jan-16\nNBRS Financial,Rising Sun,MD,4862,Howard Bank,17-Oct-14,26-Mar-15\n\"GreenChoice Bank, fsb\",Chicago,IL,28462,\"Providence Bank, LLC\",25-Jul-14,28-Jul-15\nEastside Commercial Bank,Conyers,GA,58125,Community: Southern Bank,18-Jul-14,28-Jul-15\nThe Freedom State Bank ,Freedom,OK,12483,Alva State Bank & Trust Company,27-Jun-14,25-Mar-16\nValley Bank,Fort Lauderdale,FL,21793,\"Landmark Bank, National Association\",20-Jun-14,29-Jun-15\nValley Bank,Moline,IL,10450,Great Southern Bank,20-Jun-14,26-Jun-15\nSlavie Federal Savings Bank,Bel Air,MD,32368,\"Bay Bank, FSB\",30-May-14,15-Jun-15\nColumbia Savings Bank,Cincinnati,OH,32284,\"United Fidelity Bank, fsb\",23-May-14,28-May-15\nAztecAmerica Bank ,Berwyn,IL,57866,Republic Bank of Chicago,16-May-14,18-Jul-14\nAllendale County Bank,Fairfax,SC,15062,Palmetto State Bank,25-Apr-14,18-Jul-14\nVantage Point Bank,Horsham,PA,58531,First Choice Bank,28-Feb-14,3-Mar-15\n\"Millennium Bank, National\n Association\",Sterling,VA,35096,WashingtonFirst Bank,28-Feb-14,3-Mar-15\nSyringa Bank,Boise,ID,34296,Sunwest Bank,31-Jan-14,12-Apr-16\nThe Bank of Union,El Reno,OK,17967,BancFirst,24-Jan-14,25-Mar-16\nDuPage National Bank,West Chicago,IL,5732,Republic Bank of Chicago,17-Jan-14,19-F\n",
        'DoubleQuotes' => "Bank Name,City,ST,CERT,Acquiring Institution,Closing Date,Updated Date\nFirst CornerStone Bank,\"The Very Holiest \"\"King\nof\nPrussia\"\" and all his goons\",PA,35312,First-Citizens Bank & Trust Company,6-May-16,25-May-16\nTrust Company Bank,Memphis,TN,9956,\"The 'Bank\nof\nFayette' County\",29-Apr-16,25-May-16\nNorth Milwaukee State Bank,Milwaukee,WI,20364,\"First-Citizens\"\" Bank & Trust Company\",11-Mar-16,16-Jun-16\nHometown National Bank,Longview,WA,35156,\"Twin City's Bank\",2-Oct-15,13-Apr-16\nHometown National Bank,Longview,WA,35156,'Twin ''Citys'' Bank',2-Oct-15,13-Apr-16\nHometown National Bank,Longview,WA,35156,'Twin ''Citys\nNew line\nDoodad'' Bank',2-Oct-15,13-Apr-16\nHometown National Bank,Longview,WA,35156,'Twin \"Citys\nNew line\nDoodad\" Bank',2-Oct-15,13-Apr-16\n"
    );

    public function testNewInputString()
    {
        $str = new String(file_get_contents(__DIR__ . '/../files/banklist.csv'));
        $this->assertEquals($expected = "Bank Name,City,", $str->read(15));
    }

    public function testNewInputStringFromVariableReadLine()
    {
        $str = new String($this->CSVstrings['QuoteMinimal']);
        $this->assertEquals("Bank Name,City,ST,CERT,Acquiring Institution,Closing Date,Updated Date", $str->readLine(null, "\n"));
        $this->assertEquals("First CornerStone Bank,\"King of\nPrussia\",PA,35312,First-Citizens Bank & Trust Company,6-May-16,25-May-16", $str->readLine(null, "\n"));
        $this->assertEquals("Trust Company Bank,Memphis,TN,9956,The Bank of Fayette County,29-Apr-16,25-May-16", $str->readLine(null, "\n"));
        $this->assertEquals("North Milwaukee State Bank,Milwaukee,WI,20364,First-Citizens Bank & Trust Company,11-Mar-16,16-Jun-16", $str->readLine(null, "\n"));
    }

    public function testStringHandlesQuotedLineTerminatorsTrait()
    {
        $stream = new String($this->CSVstrings['QuoteMinimal']);
        $stream->readLine(null, "\n");
        $this->assertEquals($expected = "First CornerStone Bank,\"King of\nPrussia\",PA,35312,First-Citizens Bank & Trust Company,6-May-16,25-May-16", $stream->readLine(null, "\n"));
    }

    public function testInputHandlesLineTerminatorsWithinDoubleDoubleQuotedStringsWithinSingleDoubleQuotedStrings()
    {
        $stream = new String($this->CSVstrings['DoubleQuotes']);
        $stream->readLine(null, "\n");
        $this->assertEquals($expected = "First CornerStone Bank,\"The Very Holiest \"\"King\nof\nPrussia\"\" and all his goons\",PA,35312,First-Citizens Bank & Trust Company,6-May-16,25-May-16", $stream->readLine(null, "\n"));
    }

    // also need to test the opposite, double quoted strings within single quoted strings
    public function testInputHandlesSingleQuotesAndApostrophesCorrectWithRespectToLineTerminators()
    {
        $stream = new String($this->CSVstrings['DoubleQuotes']);
        $stream->readLine(null, "\n");
        $stream->readLine(null, "\n");
        $this->assertEquals($expected = "Trust Company Bank,Memphis,TN,9956,\"The 'Bank\nof\nFayette' County\",29-Apr-16,25-May-16", $stream->readLine(null, "\n"), "Ensure single-single-quoted newlines are handled properly");
        $this->assertEquals($expected = "North Milwaukee State Bank,Milwaukee,WI,20364,\"First-Citizens\"\" Bank & Trust Company\",11-Mar-16,16-Jun-16", $stream->readLine(null, "\n"), "Ensure double-double quotes work as expected even if there is no closing double-double quote");
        $this->assertEquals($expected = "Hometown National Bank,Longview,WA,35156,\"Twin City's Bank\",2-Oct-15,13-Apr-16", $stream->readLine(null, "\n"), "Ensure single single quote (apostrophe) is handled properly");
        $this->assertEquals($expected = "Hometown National Bank,Longview,WA,35156,'Twin ''Citys'' Bank',2-Oct-15,13-Apr-16", $stream->readLine(null, "\n"), "Ensure double-single quoted strings within strings quoted by single-single quotes work as expected");
        $this->assertEquals($expected = "Hometown National Bank,Longview,WA,35156,'Twin ''Citys\nNew line\nDoodad'' Bank',2-Oct-15,13-Apr-16", $stream->readLine(null, "\n"), "Ensure that newlines that fall within double-single quoted strings within strings quoted by single-single quotes work as expected");
        $this->assertEquals($expected = "Hometown National Bank,Longview,WA,35156,'Twin \"Citys\nNew line\nDoodad\" Bank',2-Oct-15,13-Apr-16", $stream->readLine(null, "\n"), "Ensure that single double quoted newlines work as expected within single single quoted strings.");
    }
}
