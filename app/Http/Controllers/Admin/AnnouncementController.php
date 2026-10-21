<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUpdateAnnouncement;
use App\Models\Announcement;
use App\Models\Issuing;

/**
 * Informativos do órgão (CRUD simples, Model direto — ver docs/specs/architecture.md).
 * Gestor de órgão (não admin) só vê e publica os do próprio órgão; admin escolhe o órgão.
 */
class AnnouncementController extends Controller
{
    public function __construct(
        protected Announcement $repository
    ) {
        $this->middleware(['can:announcements']);
    }

    public function index()
    {
        $announcements = $this->scoped()
            ->with('issuing')
            ->orderByDesc('created_at')
            ->paginate();

        return view('admin.pages.announcements.index', compact('announcements'));
    }

    public function create()
    {
        return view('admin.pages.announcements.create', ['issuings' => $this->issuingOptions()]);
    }

    public function store(StoreUpdateAnnouncement $request)
    {
        $data = $request->validated();
        $data['tenant_id'] = auth()->user()->tenant_id;
        $data['created_by_user_id'] = auth()->id();
        $data['issuing_id'] = $this->managerIssuingId() ?? $data['issuing_id'];
        $data['published_at'] = $data['is_published'] ? now() : null;

        $this->repository->create($data);

        return redirect()->route('announcements.index')->with('messageSuccess', 'Informativo cadastrado com sucesso.');
    }

    public function edit($id)
    {
        $announcement = $this->scoped()->findOrFail($id);

        return view('admin.pages.announcements.edit', [
            'announcement' => $announcement,
            'issuings' => $this->issuingOptions(),
        ]);
    }

    public function update(StoreUpdateAnnouncement $request, $id)
    {
        $announcement = $this->scoped()->findOrFail($id);

        $data = $request->validated();
        $data['issuing_id'] = $this->managerIssuingId() ?? $data['issuing_id'];
        // Data de publicação é a da primeira vez que foi publicado; despublicar não apaga
        if ($data['is_published'] && ! $announcement->published_at) {
            $data['published_at'] = now();
        }

        $announcement->update($data);

        return redirect()->route('announcements.index')->with('messageSuccess', 'Informativo atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $this->scoped()->findOrFail($id)->delete();

        return redirect()->route('announcements.index')->with('messageSuccess', 'Informativo excluído com sucesso.');
    }

    /**
     * Tenant do usuário e, para gestor (não admin), só o órgão dele.
     */
    private function scoped()
    {
        return $this->repository->newQuery()
            ->where('tenant_id', auth()->user()->tenant_id)
            ->when($this->managerIssuingId(), fn ($q, $issuingId) => $q->where('issuing_id', $issuingId));
    }

    private function managerIssuingId(): ?int
    {
        $user = auth()->user();

        return $user->isManager() && ! $user->isAdmin() ? $user->issuing_id : null;
    }

    private function issuingOptions()
    {
        return $this->managerIssuingId() ? collect() : Issuing::orderBy('name')->get(['id', 'name']);
    }
}
