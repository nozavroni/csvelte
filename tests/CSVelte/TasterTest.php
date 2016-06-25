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
        $this->testData = "Bank Name,City,ST,CERT,Acquiring Institution,Closing Date,Updated Date\nFirst CornerStone Bank,King of\nPrussia,PA,35312,First-Citizens Bank & Trust Company,6-May-16,25-May-16\nTrust Company Bank,Memphis,TN,9956,The Bank of Fayette County,29-Apr-16,25-May-16\nNorth Milwaukee State Bank,Milwaukee,WI,20364,First-Citizens Bank & Trust Company,11-Mar-16,16-Jun-16\nHometown National Bank,Longview,WA,35156,Twin City Bank,2-Oct-15,13-Apr-16\nThe Bank of Georgia,Peachtree City,GA,35259,Fidelity Bank,2-Oct-15,13-Apr-16\nPremier Bank,Denver,CO,34112,\"United Fidelity \r\n \r \r \n \r\n Bank, fsb\",10-Jul-15,17-Dec-15\nEdgebrook Bank,Chicago,IL,57772,Republic Bank of Chicago,8-May-15,2-Jun-16\nDoral Bank,San Juan,PR,32102,Banco Popular de Puerto Rico,27-Feb-15,13-May-15\nCapitol City Bank & Trust Company,Atlanta,GA,33938,First-Citizens Bank & Trust Company,13-Feb-15,21-Apr-15\nHighland Community Bank,Chicago,IL,20290,\"United Fidelity Bank, fsb\",23-Jan-15,21-Apr-15\nFirst National Bank of Crestview ,Crestview,FL,17557,First NBC Bank,16-Jan-15,15-Jan-16\nNorthern Star Bank,Mankato,MN,34983,BankVista,19-Dec-14,6-Jan-16\n\"Frontier Bank, FSB D/B/A El Paseo Bank\",Palm Desert,CA,34738,\"Bank of Southern California, N.A.\",7-Nov-14,6-Jan-16\nThe National Republic Bank of Chicago,Chicago,IL,916,State Bank of Texas,24-Oct-14,6-Jan-16\nNBRS Financial,Rising Sun,MD,4862,Howard Bank,17-Oct-14,26-Mar-15\n\"GreenChoice Bank, fsb\",Chicago,IL,28462,\"Providence Bank, LLC\",25-Jul-14,28-Jul-15\nEastside Commercial Bank,Conyers,GA,58125,Community & Southern Bank,18-Jul-14,28-Jul-15\nThe Freedom State Bank ,Freedom,OK,12483,Alva State Bank & Trust Company,27-Jun-14,25-Mar-16\nValley Bank,Fort Lauderdale,FL,21793,\"Landmark Bank, National Association\",20-Jun-14,29-Jun-15\nValley Bank,Moline,IL,10450,Great Southern Bank,20-Jun-14,26-Jun-15\nSlavie Federal Savings Bank,Bel Air,MD,32368,\"Bay Bank, FSB\",30-May-14,15-Jun-15\nColumbia Savings Bank,Cincinnati,OH,32284,\"United Fidelity Bank, fsb\",23-May-14,28-May-15\nAztecAmerica Bank ,Berwyn,IL,57866,Republic Bank of Chicago,16-May-14,18-Jul-14\nAllendale County Bank,Fairfax,SC,15062,Palmetto State Bank,25-Apr-14,18-Jul-14\nVantage Point Bank,Horsham,PA,58531,First Choice Bank,28-Feb-14,3-Mar-15\n\"Millennium Bank, National\n Association\",Sterling,VA,35096,WashingtonFirst Bank,28-Feb-14,3-Mar-15\nSyringa Bank,Boise,ID,34296,Sunwest Bank,31-Jan-14,12-Apr-16\nThe Bank of Union,El Reno,OK,17967,BancFirst,24-Jan-14,25-Mar-16\nDuPage National Bank,West Chicago,IL,5732,Republic Bank of Chicago,17-Jan-14,19-F\n";
    }

    protected function prepareInputMock()
    {
        return $input = m::mock('CSVelte\Input\InputInterface', function($mock) {
            $crdata = str_replace(chr(Taster::LINE_FEED), chr(Taster::CARRIAGE_RETURN), $this->testData);
            $crlfdata = str_replace(chr(Taster::LINE_FEED), chr(Taster::CARRIAGE_RETURN).chr(Taster::LINE_FEED), $this->testData);
            $mock->shouldReceive('read', [2500])
                ->andReturn($this->testData, $crdata, $crlfdata);
        });
    }

    public function testTasteReturnsFlavor()
    {
        $input = $this->prepareInputMock();
        $taster = new Taster($input);
        $this->assertInstanceOf(Flavor::class, $taster->taste());
    }

    public function testGuessLineTerminator()
    {
        $input = $this->prepareInputMock();
        $taster = new Taster($input);
        $this->assertEquals(Taster::LINE_FEED, ord($taster->guessLineEndings()));

        $taster = new Taster($input);
        $this->assertEquals(Taster::CARRIAGE_RETURN, ord($taster->guessLineEndings()));

        $taster = new Taster($input);
        $this->assertEquals(chr(Taster::CARRIAGE_RETURN) . chr(Taster::LINE_FEED), $taster->guessLineEndings());
    }
}
