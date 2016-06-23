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

    protected function prepareInputMock()
    {
        return $input = m::mock('CSVelte\Input\InputInterface');
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
        $this->assertEquals("\n", $taster->guessLineTerminator());
    }

    /**
     * Just a simple test to get things started...
    public function testTasterDetectLineTerminator()
    {
        $input = $this->prepareInputMock();
        // should call $input->read(1000) to get a thousand characters or whatever
        $input->shouldReceive('read', 2500)->andReturns("\
Bank Name,City,ST,CERT,Acquiring Institution,Closing Date,Updated Date
First CornerStone Bank,King of Prussia,PA,35312,First-Citizens Bank & Trust Company,6-May-16,25-May-16
Trust Company Bank,Memphis,TN,9956,The Bank of Fayette County,29-Apr-16,25-May-16
North Milwaukee State Bank,Milwaukee,WI,20364,First-Citizens Bank & Trust Company,11-Mar-16,16-Jun-16
Hometown National Bank,Longview,WA,35156,Twin City Bank,2-Oct-15,13-Apr-16
The Bank of Georgia,Peachtree City,GA,35259,Fidelity Bank,2-Oct-15,13-Apr-16
Premier Bank,Denver,CO,34112,\"United Fidelity Bank, fsb\",10-Jul-15,17-Dec-15
Edgebrook Bank,Chicago,IL,57772,Republic Bank of Chicago,8-May-15,2-Jun-16
Doral Bank,San Juan,PR,32102,Banco Popular de Puerto Rico,27-Feb-15,13-May-15
Capitol City Bank & Trust Company,Atlanta,GA,33938,First-Citizens Bank & Trust Company,13-Feb-15,21-Apr-15
Highland Community Bank,Chicago,IL,20290,\"United Fidelity Bank, fsb\",23-Jan-15,21-Apr-15
First National Bank of Crestview ,Crestview,FL,17557,First NBC Bank,16-Jan-15,15-Jan-16
Northern Star Bank,Mankato,MN,34983,BankVista,19-Dec-14,6-Jan-16
\"Frontier Bank, FSB D/B/A El Paseo Bank\",Palm Desert,CA,34738,\"Bank of Southern California, N.A.\",7-Nov-14,6-Jan-16
The National Republic Bank of Chicago,Chicago,IL,916,State Bank of Texas,24-Oct-14,6-Jan-16
NBRS Financial,Rising Sun,MD,4862,Howard Bank,17-Oct-14,26-Mar-15
\"GreenChoice Bank, fsb\",Chicago,IL,28462,\"Providence Bank, LLC\",25-Jul-14,28-Jul-15
Eastside Commercial Bank,Conyers,GA,58125,Community & Southern Bank,18-Jul-14,28-Jul-15
The Freedom State Bank ,Freedom,OK,12483,Alva State Bank & Trust Company,27-Jun-14,25-Mar-16
Valley Bank,Fort Lauderdale,FL,21793,\"Landmark Bank, National Association\",20-Jun-14,29-Jun-15
Valley Bank,Moline,IL,10450,Great Southern Bank,20-Jun-14,26-Jun-15
Slavie Federal Savings Bank,Bel Air,MD,32368,\"Bay Bank, FSB\",30-May-14,15-Jun-15
Columbia Savings Bank,Cincinnati,OH,32284,\"United Fidelity Bank, fsb\",23-May-14,28-May-15
AztecAmerica Bank ,Berwyn,IL,57866,Republic Bank of Chicago,16-May-14,18-Jul-14
Allendale County Bank,Fairfax,SC,15062,Palmetto State Bank,25-Apr-14,18-Jul-14
Vantage Point Bank,Horsham,PA,58531,First Choice Bank,28-Feb-14,3-Mar-15
\"Millennium Bank, National Association\",Sterling,VA,35096,WashingtonFirst Bank,28-Feb-14,3-Mar-15
Syringa Bank,Boise,ID,34296,Sunwest Bank,31-Jan-14,12-Apr-16
The Bank of Union,El Reno,OK,17967,BancFirst,24-Jan-14,25-Mar-16
DuPage National Bank,West Chicago,IL,5732,Republic Bank of Chicago,17-Jan-14,19-F");
        $taster = new Taster($input);
        $lt = $taster->guessLineTerminator();
        $this->assertEquals("\n", $lt);
    }
    */
}
