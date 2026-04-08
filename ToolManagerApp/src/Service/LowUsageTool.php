<?php

namespace App\Service;


use App\Entity\Tools;
use Doctrine\ORM\EntityManagerInterface;


class LowUsageTool{
        public function __construct (
            Private EntityManagerInterface $entityManager,
    )
    {}
    public function getLowUsageTool():array
    {
        $data = [];
        $savingsAnalysis=[];
        $tools = $this->entityManager->getRepository(Tools::class)->findAll();

        $potentialMonthlySavings=0;
        $warningLevel='';
        $lowestUsageTool=null;

        foreach ($tools as $tool){

            $activeUser = $tool->getActiveUsersCount();
            $costPerUser = $activeUser > 0 ? round($tool->getMonthlyCost() / $activeUser, 2) : 0;

            $warningLevel = match(true) {
                $costPerUser < 20 =>'low',
                $costPerUser < 50 =>'medium',
                default =>'high',
            };

            $potentialAction = match($warningLevel){
                'high' => 'Consider canceling or downgrading',
                'medium' => 'Review usage and consider optimizing',
                'low' => 'Monitor usage trends',
                default => 'No action required'
            };

            if ($warningLevel==="high" || $warningLevel==="medium"){
                $potentialMonthlySavings += $tool->getMonthlyCost();
            }

            $data[]=[
                "id"=>$tool->getId(),
                "name"=>$tool->getName(),
                "monthly_cost"=>$tool->getMonthlyCost(),
                "active_user_count"=>$tool->getActiveUsersCount(),
                "cost_per_user"=>$costPerUser,
                "vendor"=>$tool->getVendor(),
                "warning_level"=>$warningLevel,
                "potential_actions"=>$potentialAction,
            ];

            if ($lowestUsageTool === null || $tool->getActiveUsersCount() < $lowestUsageTool->getActiveUsersCount()){
                $lowestUsageTool = $tool;
            }
        }

        return [
            "data"=>$data,
            "savings_analysis"=>[
                "total_underutilized_tools"=> $lowestUsageTool->getName(),
                "potential_monthly_savings"=> round($potentialMonthlySavings,2) ,
                "potential_annual_savings"=> round($potentialMonthlySavings *12,2) ,
            ],
        ];
    }

}
