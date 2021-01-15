<?php declare(strict_types = 1);
/**
 * IDEA Connection load export/import CSV data conversion - Calculation class for Statika framework
 * (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations
 */

namespace Statika\Calculations;

use Statika\Ec;
use Statika\EurocodeInterface;

Class IdeaLoadConversion
{
    /**
     * @param Ec $ec
     */
    public function calc(EurocodeInterface $ec): void
    {
        $ec->note('Két különböző szoftverből származó IDEA modell közti exportált/imortált teheradatok (CSV) konveriója.');

        $ec->numeric('beam_number', ['', 'Csomópontba csatlakozó rudak száma'], 4);

        $ec->html(<<<EOS
            <script>
                function onFileLoadAlt(elementId, event) {
                    document.getElementById(elementId).value = event.target.result;
                }
            </script>
            EOS);
        $ec->html('<input type="file" data-script="on change call onChooseFile(event, onFileLoadAlt.bind(this, \'uploader_content\')) then call toast(\'Futtasd a számítást a feldolgozáshoz\', \'success\')" class="my-3"><input type="hidden" name="uploader_content" id="uploader_content" class="" value="'.$_POST['uploader_content'].'">');
        $ec->note('IDEA esxportált CSV fájlt kell betölteni, majd futtatni a számítást.');

        if($_POST['uploader_content']) {
            $csv = $_POST['uploader_content'];
            $ec->region0('csv', 'Nyers CSV');
                $ec->pre($csv);
            $ec->region1();
        }

        if ($csv) {
            $lines = str_getcsv($csv, PHP_EOL);

            $lineArray = [];
            foreach($lines as $line) {
                $lineArray[] = str_getcsv($line, "\t");
            }

            // Remove column header
            array_shift($lineArray);

            // Get export order
            $mappingDefault = [];
            $originalOrderAsList = '';
            for ($i = 0; $i < $ec->beam_number; $i++) {
                $mappingDefault[] = [$i, $lineArray[$i][1], '', $i];
                $originalOrderAsList .= $i.' ';
            }

            $mappingFields = [
                ['name' => 'export_order', 'title' => 'Exportált sorrend', 'type' => 'text'],
                ['name' => 'axis', 'title' => 'Exportált rúd név', 'type' => 'text'],
                ['name' => 'tekla', 'title' => 'Cél modell rúd név', 'type' => 'input'],
                ['name' => 'import_order', 'title' => 'Importálás új sorrendje', 'type' => 'input'],
            ];

            $ec->bulk('mapping', $mappingFields, $mappingDefault, false);

            // Keep only forces
            foreach($lineArray as &$line) {
                array_shift($line);
                array_shift($line);
                array_shift($line);
                array_pop($line);
            }

            $blockArray = array_chunk($lineArray, (int)$ec->beam_number, false);

            $newBlockArray = [];
            foreach ($blockArray as $loadCombinationKey => $loadCombinationData) {
                $beamArray = [];
                foreach ($loadCombinationData as $beamKey => $beamData) {
                    $beamArray[(int)$ec->mapping[$beamKey]['import_order']] = $beamData;
                }
                ksort($beamArray);
                $newBlockArray[$loadCombinationKey] = $beamArray;
            }

            $ec->note('Kimenet - ez másolható be az első erő cellába (LE1 N). Ez a lista a egy tehereseten belül az *Importálás új sorrendjében* tartalmazza az erőket.');

            $pre = '';
            foreach ($newBlockArray as $blocks) {
                foreach ($blocks as $line) {
                    foreach ($line as $col) {
                        $pre .= "$col\t";
                    }
                    $pre .= "\n";
                }
            }

            $ec->html('<textarea cols="100" rows="20" class="form-control" id="import_csv">'.$pre.'</textarea>');
        }
    }
}
