<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class WorkflowController extends Controller
{
    public function index()
    {
        return $this->success(
            user()->workflows()->paginate()
        );
    }

    /**
     * @throws Throwable
     */
    public function store(Request $request)
    {
        $this->validate(request(), [
            'name' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $content = json_decode(request('content'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->error('文件内容不是有效的 JSON');
        }

        $workflow = null;
        DB::transaction(function () use (&$workflow) {
            $workflow = user()->workflows()->create([
                'name' => request('name'),
                'content' => request('content'),
            ]);
        });

        if ($workflow) {
            return $this->success($workflow);
        }

        return $this->error('创建工作流失败');
    }

    public function show(string $id)
    {
        $workflow = user()->workflows()->findOrFail($id);

        return $this->success($workflow);
    }

    public function update(Request $request, string $id)
    {
        $this->validate(request(), [
            'name' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $content = json_decode(request('content'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->error('文件内容不是有效的 JSON');
        }

        $workflow = user()->workflows()->findOrFail($id);
        $workflow->update([
            'name' => request('name'),
            'content' => $content,
        ]);

        return $this->success();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $workflow = user()->workflows()->findOrFail($id);

        if ($workflow->delete()) {
            return $this->success();
        }

        return $this->error('删除工作流失败');
    }
}
