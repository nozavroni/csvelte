<?php
/**
 * TasterTest
 *
 * @package   CSVelte Unit Tests
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
use PHPUnit\Framework\TestCase;
use Mockery as m;
use Mockery\Adapter\PHPUnit\MockeryPHPUnitIntegration;
use CSVelte\CSVelte;
use CSVelte\Flavor;
use CSVelte\Taster;
use CSVelte\Input\InputInterface;

class TasterTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function setUp()
    {
        $this->testData = "Bank Name,City,ST,CERT,Acquiring Institution,Closing Date,Updated Date\nFirst CornerStone Bank,\"King of\nPrussia\",PA,35312,First-Citizens Bank & Trust Company,6-May-16,25-May-16\nTrust Company Bank,Memphis,TN,9956,The Bank of Fayette County,29-Apr-16,25-May-16\nNorth Milwaukee State Bank,Milwaukee,WI,20364,First-Citizens Bank & Trust Company,11-Mar-16,16-Jun-16\nHometown National Bank,Longview,WA,35156,Twin City Bank,2-Oct-15,13-Apr-16\nThe Bank of Georgia,Peachtree City,GA,35259,Fidelity Bank,2-Oct-15,13-Apr-16\nPremier Bank,Denver,CO,34112,\"United Fidelity \r\n \r \r \n \r\n Bank, fsb\",10-Jul-15,17-Dec-15\nEdgebrook Bank,Chicago,IL,57772,Republic Bank of Chicago,8-May-15,2-Jun-16\nDoral Bank,San Juan,PR,32102,Banco Popular de Puerto Rico,27-Feb-15,13-May-15\nCapitol City Bank & Trust Company,Atlanta,GA,33938,First-Citizens Bank & Trust Company,13-Feb-15,21-Apr-15\nHighland Community Bank,Chicago,IL,20290,\"United Fidelity Bank, fsb\",23-Jan-15,21-Apr-15\nFirst National Bank of Crestview ,Crestview,FL,17557,First NBC Bank,16-Jan-15,15-Jan-16\nNorthern Star Bank,Mankato,MN,34983,BankVista,19-Dec-14,6-Jan-16\n\"Frontier Bank, FSB D/B/A El Paseo Bank\",Palm Desert,CA,34738,\"Bank of Southern California, N.A.\",7-Nov-14,6-Jan-16\nThe National Republic Bank of Chicago,Chicago,IL,916,State Bank of Texas,24-Oct-14,6-Jan-16\nNBRS Financial,Rising Sun,MD,4862,Howard Bank,17-Oct-14,26-Mar-15\n\"GreenChoice Bank, fsb\",Chicago,IL,28462,\"Providence Bank, LLC\",25-Jul-14,28-Jul-15\nEastside Commercial Bank,Conyers,GA,58125,Community & Southern Bank,18-Jul-14,28-Jul-15\nThe Freedom State Bank ,Freedom,OK,12483,Alva State Bank & Trust Company,27-Jun-14,25-Mar-16\nValley Bank,Fort Lauderdale,FL,21793,\"Landmark Bank, National Association\",20-Jun-14,29-Jun-15\nValley Bank,Moline,IL,10450,Great Southern Bank,20-Jun-14,26-Jun-15\nSlavie Federal Savings Bank,Bel Air,MD,32368,\"Bay Bank, FSB\",30-May-14,15-Jun-15\nColumbia Savings Bank,Cincinnati,OH,32284,\"United Fidelity Bank, fsb\",23-May-14,28-May-15\nAztecAmerica Bank ,Berwyn,IL,57866,Republic Bank of Chicago,16-May-14,18-Jul-14\nAllendale County Bank,Fairfax,SC,15062,Palmetto State Bank,25-Apr-14,18-Jul-14\nVantage Point Bank,Horsham,PA,58531,First Choice Bank,28-Feb-14,3-Mar-15\n\"Millennium Bank, National\n Association\",Sterling,VA,35096,WashingtonFirst Bank,28-Feb-14,3-Mar-15\nSyringa Bank,Boise,ID,34296,Sunwest Bank,31-Jan-14,12-Apr-16\nThe Bank of Union,El Reno,OK,17967,BancFirst,24-Jan-14,25-Mar-16\nDuPage National Bank,West Chicago,IL,5732,Republic Bank of Chicago,17-Jan-14,19-F\n";
        $this->testTabSingleData = "Bank Name\tCity\tST\tCERT\tAcquiring Institution\tClosing Date\tUpdated Date\nFirst CornerStone Bank\tKing of Prussia\tPA\t35312\tFirst-Citizens Bank & Trust Company\t6-May-16\t25-May-16\nTrust Company Bank\tMemphis\tTN\t9956\tThe Bank of Fayette County\t29-Apr-16\t25-May-16\nNorth Milwaukee State Bank\tMilwaukee\tWI\t20364\tFirst-Citizens Bank & Trust Company\t11-Mar-16\t16-Jun-16\nHometown National Bank\tLongview\tWA\t35156\tTwin City Bank\t2-Oct-15\t13-Apr-16\nThe Bank of Georgia\tPeachtree City\tGA\t35259\tFidelity Bank\t2-Oct-15\t13-Apr-16\nPremier Bank\tDenver\tCO\t34112\t'United Fidelity \r\n \r \r \n \r\n Bank\t fsb'\t10-Jul-15\t17-Dec-15\nEdgebrook Bank\tChicago\tIL\t57772\tRepublic Bank of Chicago\t8-May-15\t2-Jun-16\nDoral Bank\tSan Juan\tPR\t32102\tBanco Popular de Puerto Rico\t27-Feb-15\t13-May-15\nCapitol City Bank & Trust Company\tAtlanta\tGA\t33938\tFirst-Citizens Bank & Trust Company\t13-Feb-15\t21-Apr-15\nHighland Community Bank\tChicago\tIL\t20290\t'United Fidelity Bank, fsb'\t23-Jan-15\t21-Apr-15\nFirst National Bank of Crestview \tCrestview\tFL\t17557\tFirst NBC Bank\t16-Jan-15\t15-Jan-16\nNorthern Star Bank\tMankato\tMN\t34983\tBankVista\t19-Dec-14\t6-Jan-16\n'Frontier\'s Bank, FSB D/B/A El Paseo Bank'\tPalm Desert\tCA\t34738\t'Bank of Southern California, N.A.'\t7-Nov-14\t6-Jan-16\nThe National Republic Bank of Chicago\tChicago\tIL\t916\tState Bank of Texas\t24-Oct-14\t6-Jan-16\nNBRS Financial\tRising Sun\tMD\t4862\tHoward Bank\t17-Oct-14\t26-Mar-15\n'GreenChoice\'s Bank, fsb'\tChicago\tIL\t28462\t'Providence Bank, LLC'\t25-Jul-14\t28-Jul-15\nEastside Commercial Bank\tConyers\tGA\t58125\tCommunity & Southern Bank\t18-Jul-14\t28-Jul-15\nThe Freedom State Bank \tFreedom\tOK\t12483\tAlva State Bank & Trust Company\t27-Jun-14\t25-Mar-16\nValley Bank\tFort Lauderdale\tFL\t21793\t'Landmark Bank, National Association'\t20-Jun-14\t29-Jun-15\nValley Bank\tMoline\tIL\t10450\tGreat Southern Bank\t20-Jun-14\t26-Jun-15\nSlavie Federal Savings Bank\tBel Air\tMD\t32368\t'Bay Bank, FSB'\t30-May-14\t15-Jun-15\nColumbia Savings Bank\tCincinnati\tOH\t32284\t'United Fidelity Bank, fsb'\t23-May-14\t28-May-15\nAztecAmerica Bank \tBerwyn\tIL\t57866\tRepublic Bank of Chicago\t16-May-14\t18-Jul-14\nAllendale County Bank\tFairfax\tSC\t15062\tPalmetto State Bank\t25-Apr-14\t18-Jul-14\nVantage Point Bank\tHorsham\tPA\t58531\tFirst Choice Bank\t28-Feb-14\t3-Mar-15\n'Millennium Bank, National\n Association'\tSterling\tVA\t35096\tWashingtonFirst Bank\t28-Feb-14\t3-Mar-15\nSyringa Bank\tBoise\tID\t34296\tSunwest Bank\t31-Jan-14\t12-Apr-16\nThe Bank of Union\tEl Reno\tOK\t17967\tBancFirst\t24-Jan-14\t25-Mar-16\nDuPage National Bank\tWest Chicago\tIL\t5732\tRepublic Bank of Chicago\t17-Jan-14\t19-F\n";
        $this->testNoQuoteComma = "1,Eldon Base for stackable storage shelf platinum,Muhammed MacIntyre,3,-213.25,38.94,35,Nunavut,Storage & Organization,0.8\n2,1.7 Cubic Foot Compact Office Refrigerators,Barry French,293,457.81,208.16,68.02,Nunavut,Appliances,0.58\n3,Cardinal Slant-DÆ Ring Binder Heavy Gauge Vinyl,Barry French,293,46.71,8.69,2.99,Nunavut,Binders and Binder Accessories,0.39\n4,R380,Clay Rozendal,483,1198.97,195.99,3.99,Nunavut,Telephones and Communication,0.58\n5,Holmes HEPA Air Purifier,Carlos Soltero,515,30.94,21.78,5.94,Nunavut,Appliances,0.5\n6,G.E. Longer-Life Indoor Recessed Floodlight Bulbs,Carlos Soltero,515,4.43,6.64,4.95,Nunavut,Office Furnishings,0.37\n7,Angle-D Binders with Locking Rings Label Holders,Carl Jackson,613,-54.04,7.3,7.72,Nunavut,Binders and Binder Accessories,0.38\n8,SAFCO Mobile Desk Side File Wire Frame,Carl Jackson,613,127.70,42.76,6.22,Nunavut,Storage & Organization,\n9,SAFCO Commercial Wire Shelving Black,Monica Federle,643,-695.26,138.14,35,Nunavut,Storage & Organization,\n10,Xerox 198,Dorothy Badders,678,-226.36,4.98,8.33,Nunavut,Paper,0.38";
    }

    protected function prepareInputMock($for)
    {
        switch ($for) {
            case 'TasterTest::testGuessLineTerminator':
                return $input = m::mock('CSVelte\Input\InputInterface', function($mock) {
                    $crdata = str_replace(chr(Taster::LINE_FEED), chr(Taster::CARRIAGE_RETURN), $this->testData);
                    $crlfdata = str_replace(chr(Taster::LINE_FEED), chr(Taster::CARRIAGE_RETURN).chr(Taster::LINE_FEED), $this->testData);
                    $mock->shouldReceive('read', [2500])
                        ->andReturn($this->testData, $crdata, $crlfdata);
                });
            case 'TasterTest::testLickQuoteAndDelimFailsWithNoQuoteColumns':
                return $input = m::mock('CSVelte\Input\InputInterface', function($mock) {
                    $mock->shouldReceive('read', [2500])
                        ->andReturn($this->testNoQuoteComma);
                });
            case 'TasterTest::testLickQuoteAndDelim':
            default:
                return $input = m::mock('CSVelte\Input\InputInterface', function($mock) {
                    $mock->shouldReceive('read', [2500])
                        ->andReturn($this->testData, $this->testTabSingleData);
                });
        }
    }

    public function testLickReturnsFlavor()
    {
        $input = $this->prepareInputMock(__METHOD__);
        $taster = new Taster($input);
        $this->assertInstanceOf(Flavor::class, $taster->lick());
    }

    public function testGuessLineTerminator()
    {
        $input = $this->prepareInputMock(__METHOD__);
        $taster = new Taster($input);
        $this->assertEquals(Taster::LINE_FEED, ord($taster->lickLineEndings()));

        $taster = new Taster($input);
        $this->assertEquals(Taster::CARRIAGE_RETURN, ord($taster->lickLineEndings()));

        $taster = new Taster($input);
        $this->assertEquals(chr(Taster::CARRIAGE_RETURN) . chr(Taster::LINE_FEED), $taster->lickLineEndings());
    }

    public function testLickQuoteAndDelim()
    {
        $input = $this->prepareInputMock(__METHOD__);
        $taster = new Taster($input);
        $this->assertEquals(array('"', ','), $taster->lickQuoteAndDelim());

        $taster = new Taster($input);
        $this->assertEquals(array("'", "\t"), $taster->lickQuoteAndDelim());
    }

    /**
     * If no columns are quoted, the lick quote and delim method should not work
     */
    public function testLickQuoteAndDelimFailsWithNoQuoteColumns()
    {
        $input = $this->prepareInputMock(__METHOD__);
        $taster = new Taster($input);
        $this->assertEquals(array("", null), $taster->lickQuoteAndDelim());
    }
}
