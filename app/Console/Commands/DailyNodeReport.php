<?php

namespace App\Console\Commands;

use App\Models\Node;
use App\Models\User;
use App\Notifications\NodeDailyReport;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Log;
use Notification;

class DailyNodeReport extends Command
{
    protected $signature = 'dailyNodeReport';

    protected $description = '自动报告节点昨日使用情况';

    public function handle(): void
    {
        $jobTime = microtime(true);

        if (sysConfig('node_daily_notification')) {
            $date = date('Y-m-d', strtotime('-1 days'));
            $nodeList = Node::with('dailyDataFlows')->whereHas('dailyDataFlows', function (Builder $query) use ($date) {
                $query->whereDate('created_at', $date);
            })->get();
            if ($nodeList->isNotEmpty()) {
                $data = [];
                $upload = 0;
                $download = 0;
                foreach ($nodeList as $node) {
                    $log = $node->dailyDataFlows()->whereDate('created_at', $date)->first();
                    $data[] = [
                        'name' => $node->name,
                        'upload' => formatBytes($log->u ?? 0),
                        'download' => formatBytes($log->d ?? 0),
                        'total' => $log->traffic ?? '',
                    ];
                    $upload += $log->u ?? 0;
                    $download += $log->d ?? 0;
                }
                if ($data) {
                    $data[] = [
                        'name' => trans('notification.node.total'),
                        'total' => formatBytes($upload + $download),
                        'upload' => formatBytes($upload),
                        'download' => formatBytes($download),
                    ];

                    Notification::send(User::role('Super Admin')->get(), new NodeDailyReport($data));
                }
            }
        }

        $jobTime = round(microtime(true) - $jobTime, 4);
        Log::info(__('----「:job」Completed, Used :time seconds ----', ['job' => $this->description, 'time' => $jobTime]));
    }
}
