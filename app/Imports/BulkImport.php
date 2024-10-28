<?php
namespace App\Imports;
use App\Bulk;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow; 
use App\Zones;

class BulkImport implements ToModel,WithHeadingRow
{
	/**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {  //echo "<pre>"; print_r($row); exit;

        if(!empty($row['office_name'])) {
            $zone_name = $row['office_name'].' - '.$row['pincode'];
 
            $zone = new Zones;

            $zone->zone_name = $zone_name; 

            $zone->status = 'ACTIVE';

            $zone->position = 1;

            $zone->save();

            $id = $zone->id;

            $zone->position = $id;

            $zone->save();
        }

    }
}