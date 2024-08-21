<?php

use Illuminate\Database\Seeder;

class IdentifierFillInHistory extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Caution:: One time script to fill identifier column in bulky chat_messages_history. Raw SQL is used. Don't follow this pattern in other seeders.
        $sqlMax = "select max(id) as maxId, count(id) as total from chat_messages_history";
        $resultMax = DB::select($sqlMax);
        $maxId = $resultMax[0]->maxId;
        $total = $resultMax[0]->total;
        $limit=10000;
        $limitSourceType = 5000;
        $this->command->getOutput()->progressStart($total);
        $this->updateIdentifier(0, $maxId, $limit);
        $this->updateSourceType($limitSourceType);
    }

    public function updateIdentifier($lastDoneUpTo = 0, $maxId, $limit)
    {
        $sql = "select id from chat_messages_history where id > $lastDoneUpTo order by id asc limit $limit ";
        $results = DB::select($sql);
        $startId  = current($results)->id;
        $endId    = end($results)->id;
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::update("update chat_messages_history cmh inner join clients c on cmh.client_id = c.id set cmh.identifier=c.identifier where cmh.id >= $startId and cmh.id <=$endId");
        //$this->command->line('Identifier filled for ' . count($results), false);
        if ($endId >= $maxId) {
        	  //Last One
            DB::update("update chat_messages_history cmh inner join clients c on cmh.client_id = c.id set cmh.identifier=c.identifier where cmh.id >= $maxId");
           $this->command->getOutput()->progressFinish();
           $this->command->line('DONE! Identifier' , false);
           return;
        }
        $this->command->getOutput()->progressAdvance($limit);
        $this->updateIdentifier($endId, $maxId, $limit);
    }
    
    public function updateSourceType($limitSourceType)
    {
        $sql = "select cmh.id from `chat_messages_history` cmh inner join chat_channels cc on cmh.chat_channel_id=cc.id where cc.source_type is not null and cmh.source_type is null limit $limitSourceType";
        $results = DB::select($sql);
        $ids = [];
        foreach ($results as $id) {
            $ids[] = $id->id;
        }
        $strIds = implode(',',$ids);
        if (empty($results)){
            $this->command->line('DONE! source Type' , false);
            return;
        }
        $strIds = implode(',',$ids);
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::update("update `chat_messages_history` cmh inner join chat_channels cc on cmh.chat_channel_id=cc.id set cmh.source_type = cc.source_type where cmh.id in ($strIds)");
        $this->updateSourceType($limitSourceType);

    }

}