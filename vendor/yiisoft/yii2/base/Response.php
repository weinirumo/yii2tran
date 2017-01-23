<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * Response represents the response of an [[Application]] to a [[Request]].
 * Response表示[[Application]]到[[Request]]的响应。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Response extends Component
{
    /**
     * @var integer the exit status. Exit statuses should be in the range 0 to 254.
     * 变量 执行 退出状态。退出状态应该在0到254之间。
     *
     * The status 0 means the program terminates successfully.
     * 状态0表示该程序成功结束。
     */
    public $exitStatus = 0;


    /**
     * Sends the response to client.
     * 把响应发送到客户端。
     */
    public function send()
    {
    }

    /**
     * Removes all existing output buffers.
     * 移除所有存在的输出缓冲区。
     */
    public function clearOutputBuffers()
    {
        // the following manual level counting is to deal with zlib.output_compression set to On
        // 下面的手动水平计数取决于zlib.output_compression的设置。
        for ($level = ob_get_level(); $level > 0; --$level) {
            if (!@ob_end_clean()) {
                ob_clean();
            }
        }
    }
}
