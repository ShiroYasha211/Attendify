@extends('layouts.admin')

@section('title', 'هيكل المكتبة والصلاحيات')

@section('content')
<style>
    [x-cloak] { display: none !important; }
    .ls-page { max-width: 1480px; margin: 0 auto; color: #172033; }
    .ls-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; margin-bottom: 1.25rem; }
    .ls-title { margin: 0; font-size: 1.55rem; font-weight: 900; letter-spacing: 0; }
    .ls-subtitle { margin: .35rem 0 0; color: #667085; font-size: .9rem; }
    .ls-header-actions { display: flex; gap: .55rem; flex-wrap: wrap; }
    .ls-btn { min-height: 40px; border: 1px solid #d8dee9; border-radius: 8px; background: #fff; color: #344054; padding: .58rem .85rem; display: inline-flex; align-items: center; justify-content: center; gap: .45rem; font-weight: 800; text-decoration: none; transition: 180ms ease; }
    .ls-btn:hover { border-color: #98a2b3; color: #172033; transform: translateY(-1px); }
    .ls-btn:active { transform: translateY(1px); }
    .ls-btn.is-primary { background: #3448c5; border-color: #3448c5; color: #fff; }
    .ls-context { background: #fff; border: 1px solid #e3e7ee; border-radius: 8px; padding: 1rem; margin-bottom: 1rem; }
    .ls-context-grid { display: grid; grid-template-columns: minmax(280px, 1.4fr) minmax(260px, .8fr); gap: 1rem; align-items: end; }
    .ls-label { display: block; margin-bottom: .42rem; color: #344054; font-size: .8rem; font-weight: 900; }
    .ls-search { position: relative; }
    .ls-input, .ls-select, .ls-textarea { width: 100%; border: 1px solid #ccd3df; border-radius: 8px; background: #fff; color: #172033; padding: .7rem .8rem; outline: none; transition: border-color 160ms ease, box-shadow 160ms ease; }
    .ls-input:focus, .ls-select:focus, .ls-textarea:focus { border-color: #5266d4; box-shadow: 0 0 0 3px rgba(82, 102, 212, .13); }
    .ls-results { position: absolute; z-index: 40; inset-inline: 0; top: calc(100% + .35rem); max-height: 280px; overflow-y: auto; background: #fff; border: 1px solid #d8dee9; border-radius: 8px; box-shadow: 0 18px 40px rgba(30, 41, 59, .14); }
    .ls-result { width: 100%; border: 0; border-bottom: 1px solid #edf0f4; background: #fff; text-align: right; padding: .7rem .8rem; }
    .ls-result:hover, .ls-result:focus { background: #f3f5ff; outline: none; }
    .ls-result strong, .ls-result small { display: block; }
    .ls-result strong { font-size: .88rem; color: #172033; }
    .ls-result small { color: #7b8495; margin-top: .18rem; }
    .ls-type-switch { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .4rem; }
    .ls-type-switch a { border: 1px solid #d8dee9; border-radius: 8px; padding: .68rem; color: #667085; text-align: center; text-decoration: none; font-weight: 850; }
    .ls-type-switch a.is-active { background: #172033; border-color: #172033; color: #fff; }
    .ls-subject-path { display: flex; align-items: center; gap: .45rem; flex-wrap: wrap; margin-top: .85rem; color: #667085; font-size: .82rem; }
    .ls-subject-path strong { color: #172033; }
    .ls-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); border: 1px solid #e3e7ee; border-radius: 8px; background: #fff; margin-bottom: 1rem; overflow: hidden; }
    .ls-stat { padding: .9rem 1rem; border-inline-end: 1px solid #edf0f4; }
    .ls-stat:last-child { border-inline-end: 0; }
    .ls-stat span { display: block; color: #7b8495; font-size: .75rem; font-weight: 800; }
    .ls-stat strong { display: block; margin-top: .2rem; font-size: 1.35rem; font-weight: 950; font-variant-numeric: tabular-nums; }
    .ls-workspace { display: grid; grid-template-columns: minmax(0, 1.65fr) minmax(310px, .75fr); gap: 1rem; align-items: start; }
    .ls-tree-panel, .ls-side-panel, .ls-section { background: #fff; border: 1px solid #e3e7ee; border-radius: 8px; }
    .ls-panel-head { display: flex; align-items: center; justify-content: space-between; gap: .75rem; padding: .9rem 1rem; border-bottom: 1px solid #edf0f4; }
    .ls-panel-head h2 { margin: 0; font-size: 1rem; font-weight: 900; }
    .ls-tree { padding: .65rem; min-height: 360px; }
    .ls-node-wrap { margin-inline-start: calc(var(--node-depth) * 1rem); }
    .ls-node { display: flex; align-items: center; gap: .5rem; min-height: 58px; border-bottom: 1px solid #edf0f4; transition: background 160ms ease; }
    .ls-node:hover { background: #f8f9fc; }
    .ls-node.is-inactive { opacity: .55; }
    .ls-node-main { flex: 1; min-width: 0; display: flex; align-items: center; gap: .7rem; border: 0; background: transparent; text-align: right; padding: .55rem; }
    .ls-node-icon { width: 34px; height: 34px; flex: 0 0 34px; display: grid; place-items: center; border-radius: 7px; background: #eef1ff; color: #3448c5; }
    .ls-node-copy { min-width: 0; }
    .ls-node-copy strong, .ls-node-copy small { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .ls-node-copy strong { font-size: .87rem; color: #172033; }
    .ls-node-copy small { margin-top: .18rem; color: #7b8495; font-size: .72rem; }
    .ls-node-actions { display: flex; align-items: center; gap: .18rem; padding-inline-end: .45rem; }
    .ls-node-actions form { margin: 0; }
    .ls-icon-btn { width: 31px; height: 31px; display: grid; place-items: center; border: 1px solid transparent; border-radius: 7px; background: transparent; color: #667085; transition: 150ms ease; }
    .ls-icon-btn:hover { background: #eef1ff; color: #3448c5; border-color: #dce2ff; }
    .ls-icon-btn.is-danger:hover { background: #fff1f1; color: #c93737; border-color: #ffd5d5; }
    .ls-node-children { border-inline-start: 1px solid #dfe4ec; }
    .ls-side-panel { position: sticky; top: 88px; overflow: hidden; }
    .ls-tabs { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); border-bottom: 1px solid #edf0f4; }
    .ls-tabs button { border: 0; background: #fff; padding: .72rem .35rem; color: #7b8495; font-size: .78rem; font-weight: 850; border-bottom: 2px solid transparent; }
    .ls-tabs button.is-active { color: #3448c5; border-bottom-color: #3448c5; background: #f8f9ff; }
    .ls-form { padding: 1rem; }
    .ls-field { margin-bottom: .8rem; }
    .ls-form-title { margin: 0 0 .85rem; font-size: .95rem; font-weight: 900; }
    .ls-checks { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .4rem; }
    .ls-check { display: flex; align-items: flex-start; gap: .45rem; border: 1px solid #e3e7ee; border-radius: 7px; padding: .55rem; font-size: .75rem; font-weight: 750; }
    .ls-scopes { max-height: 170px; overflow-y: auto; border: 1px solid #e3e7ee; border-radius: 8px; padding: .4rem; }
    .ls-scope { display: flex; gap: .45rem; align-items: center; padding: .38rem; font-size: .76rem; }
    .ls-section { margin-top: 1rem; overflow: hidden; }
    .ls-table-wrap { overflow-x: auto; }
    .ls-table { width: 100%; border-collapse: collapse; min-width: 760px; }
    .ls-table th, .ls-table td { padding: .75rem .85rem; border-bottom: 1px solid #edf0f4; text-align: right; vertical-align: middle; }
    .ls-table th { background: #f8f9fc; color: #667085; font-size: .73rem; font-weight: 900; }
    .ls-table td { font-size: .8rem; }
    .ls-badges { display: flex; gap: .25rem; flex-wrap: wrap; }
    .ls-badge { border-radius: 6px; background: #edf0f6; color: #475467; padding: .23rem .4rem; font-size: .67rem; font-weight: 850; }
    .ls-badge.is-active { background: #e8f7ef; color: #16784a; }
    .ls-badge.is-off { background: #f3f4f6; color: #7b8495; }
    .ls-empty { min-height: 340px; display: grid; place-items: center; text-align: center; padding: 2rem; border: 1px dashed #cfd6e1; border-radius: 8px; background: #fafbfc; }
    .ls-empty i { font-size: 2rem; color: #98a2b3; margin-bottom: .7rem; }
    .ls-empty h2 { font-size: 1rem; font-weight: 900; margin: 0; }
    .ls-empty p { color: #7b8495; margin: .35rem 0 0; font-size: .82rem; }
    .ls-alert { border-radius: 8px; padding: .75rem .9rem; margin-bottom: 1rem; font-size: .84rem; font-weight: 750; }
    .ls-alert.is-success { background: #eaf8f0; color: #176c47; border: 1px solid #bce7cf; }
    .ls-alert.is-error { background: #fff1f1; color: #a93333; border: 1px solid #f3c3c3; }
    @media (max-width: 1100px) { .ls-workspace { grid-template-columns: 1fr; } .ls-side-panel { position: static; } }
    @media (max-width: 760px) { .ls-header, .ls-context-grid { display: grid; grid-template-columns: 1fr; } .ls-stats { grid-template-columns: repeat(2, 1fr); } .ls-stat:nth-child(2) { border-inline-end: 0; } .ls-node { align-items: flex-start; flex-wrap: wrap; padding-bottom: .4rem; } .ls-node-actions { width: 100%; justify-content: flex-end; } .ls-node-wrap { margin-inline-start: calc(var(--node-depth) * .45rem); } }
</style>

<main class="ls-page" x-data="libraryStructure()">
    <header class="ls-header">
        <div>
            <h1 class="ls-title">هيكل المكتبة والصلاحيات</h1>
            <p class="ls-subtitle">{{ $type === 'shared' ? 'المكتبة المشتركة' : 'مصادر المقرر' }}</p>
        </div>
        <div class="ls-header-actions">
            <a class="ls-btn" href="{{ route('admin.library.index') }}">
                <i class="fas fa-file-lines" aria-hidden="true"></i> قائمة الملفات
            </a>
            @if($space)
                <button type="button" class="ls-btn is-primary" @click="openAdd(null, 'الجذر')">
                    <i class="fas fa-folder-plus" aria-hidden="true"></i> قسم رئيسي
                </button>
            @endif
        </div>
    </header>

    @if(session('success'))
        <div class="ls-alert is-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="ls-alert is-error">{{ $errors->first() }}</div>
    @endif

    <section class="ls-context" aria-label="نطاق المكتبة">
        <div class="ls-context-grid">
            <div class="ls-search">
                <label class="ls-label" for="subjectLookup">المادة الدراسية</label>
                <input id="subjectLookup" class="ls-input" type="search" autocomplete="off"
                       x-model="subjectQuery" @input.debounce.250ms="searchSubjects"
                       @focus="subjectOpen = subjectResults.length > 0"
                       placeholder="ابحث باسم المادة أو الرمز أو التخصص"
                       value="{{ $subject?->name }}">
                <div class="ls-results" x-show="subjectOpen" x-cloak @click.outside="subjectOpen = false">
                    <template x-for="item in subjectResults" :key="item.id">
                        <button type="button" class="ls-result" @click="chooseSubject(item.id)">
                            <strong x-text="item.label"></strong>
                            <small x-text="item.meta"></small>
                        </button>
                    </template>
                </div>
            </div>
            <div>
                <span class="ls-label">نوع المكتبة</span>
                <div class="ls-type-switch">
                    <a class="{{ $type === 'shared' ? 'is-active' : '' }}"
                       href="{{ route('admin.library-structure.index', array_filter(['subject_id' => $subject?->id, 'type' => 'shared'])) }}">المكتبة المشتركة</a>
                    <a class="{{ $type === 'course' ? 'is-active' : '' }}"
                       href="{{ route('admin.library-structure.index', array_filter(['subject_id' => $subject?->id, 'type' => 'course'])) }}">مصادر المقرر</a>
                </div>
            </div>
        </div>
        @if($subject)
            <div class="ls-subject-path">
                <strong>{{ $subject->name }}</strong>
                @foreach(collect([$subject->major?->college?->university?->name, $subject->major?->college?->name, $subject->major?->name, $subject->level?->name, $subject->term?->name, $subject->semester?->name])->filter() as $part)
                    <i class="fas fa-chevron-left" aria-hidden="true"></i><span>{{ $part }}</span>
                @endforeach
            </div>
        @endif
    </section>

    @if(!$space)
        <section class="ls-empty">
            <div>
                <i class="fas fa-diagram-project" aria-hidden="true"></i>
                <h2>اختر مادة لعرض هيكل المكتبة</h2>
                <p>استخدم البحث للوصول إلى المادة مباشرة.</p>
            </div>
        </section>
    @else
        <section class="ls-stats" aria-label="ملخص المساحة">
            <div class="ls-stat"><span>الأقسام</span><strong>{{ $stats['nodes'] }}</strong></div>
            <div class="ls-stat"><span>الأقسام النشطة</span><strong>{{ $stats['active_nodes'] }}</strong></div>
            <div class="ls-stat"><span>ملفات المادة</span><strong>{{ $stats['resources'] }}</strong></div>
            <div class="ls-stat"><span>المساهمون النشطون</span><strong>{{ $stats['contributors'] }}</strong></div>
        </section>

        <div class="ls-workspace">
            <section class="ls-tree-panel">
                <div class="ls-panel-head">
                    <h2>شجرة الأقسام</h2>
                    <span class="ls-badge is-active">{{ $space->is_active ? 'نشطة' : 'متوقفة' }}</span>
                </div>
                <div class="ls-tree">
                    @foreach($nodeTree as $item)
                        @include('admin.library._structure_node', ['item' => $item, 'depth' => 0])
                    @endforeach
                </div>
            </section>

            <aside class="ls-side-panel">
                <div class="ls-tabs">
                    <button type="button" :class="panel === 'node' ? 'is-active' : ''" @click="panel = 'node'">القسم</button>
                    <button type="button" :class="panel === 'upload' ? 'is-active' : ''" @click="panel = 'upload'">رفع ملف</button>
                    <button type="button" :class="panel === 'permission' ? 'is-active' : ''" @click="panel = 'permission'">صلاحية</button>
                </div>

                <div x-show="panel === 'node'">
                    <form class="ls-form" method="POST" action="{{ route('admin.library-structure.nodes.store') }}" x-show="nodeMode === 'add'">
                        @csrf
                        <input type="hidden" name="library_space_id" value="{{ $space->id }}">
                        <input type="hidden" name="parent_id" :value="parentId || ''">
                        <h3 class="ls-form-title" x-text="parentLabel ? 'إضافة داخل ' + parentLabel : 'إضافة قسم رئيسي'"></h3>
                        <div class="ls-field"><label class="ls-label">اسم القسم</label><input class="ls-input" name="title" required maxlength="150"></div>
                        <div class="ls-field"><label class="ls-label">النوع</label><select class="ls-select" name="node_type"><option value="folder">مجلد</option><option value="section">قسم محتوى</option></select></div>
                        <div class="ls-field"><label class="ls-label">رمز الأيقونة</label><input class="ls-input" name="icon" value="folder" maxlength="64"></div>
                        <button class="ls-btn is-primary w-100" type="submit"><i class="fas fa-plus"></i> إضافة القسم</button>
                    </form>

                    <form class="ls-form" method="POST" :action="nodeUrl(updateNodeUrl, selectedNode.id)" x-show="nodeMode === 'edit' && selectedNode.id">
                        @csrf
                        @method('PATCH')
                        <h3 class="ls-form-title">خصائص القسم</h3>
                        <div class="ls-field"><label class="ls-label">الاسم</label><input class="ls-input" name="title" x-model="selectedNode.title" required maxlength="150"></div>
                        <div class="ls-field"><label class="ls-label">رمز الأيقونة</label><input class="ls-input" name="icon" x-model="selectedNode.icon" maxlength="64"></div>
                        <button class="ls-btn is-primary w-100" type="submit"><i class="fas fa-check"></i> حفظ التعديل</button>
                    </form>
                </div>

                <form class="ls-form" method="POST" enctype="multipart/form-data" action="{{ route('admin.library-structure.resources.upload') }}" x-show="panel === 'upload'">
                    @csrf
                    <input type="hidden" name="node_id" :value="uploadNodeId || ''">
                    <h3 class="ls-form-title" x-text="uploadNodeLabel ? 'رفع إلى ' + uploadNodeLabel : 'اختر قسماً من الشجرة'"></h3>
                    <div class="ls-field"><label class="ls-label">عنوان الملف</label><input class="ls-input" name="title" required maxlength="255" :disabled="!uploadNodeId"></div>
                    <div class="ls-field"><label class="ls-label">الملف</label><input class="ls-input" type="file" name="file" required :disabled="!uploadNodeId"></div>
                    <div class="ls-field"><label class="ls-label">الظهور</label><select class="ls-select" name="visibility"><option value="batch">المستوى</option><option value="college">الكلية</option><option value="everyone">الجميع</option></select></div>
                    <div class="ls-field"><label class="ls-label">الوصف</label><textarea class="ls-textarea" name="description" rows="2"></textarea></div>
                    <button class="ls-btn is-primary w-100" type="submit" :disabled="!uploadNodeId"><i class="fas fa-cloud-arrow-up"></i> رفع الملف</button>
                </form>

                <form class="ls-form" method="POST" action="{{ route('admin.library-structure.contributors.store') }}" x-show="panel === 'permission'">
                    @csrf
                    <input type="hidden" name="library_space_id" value="{{ $space->id }}">
                    <input type="hidden" name="user_id" :value="selectedUser.id || ''">
                    <div class="ls-field ls-search">
                        <label class="ls-label">المستخدم</label>
                        <input class="ls-input" type="search" x-model="userQuery" @input.debounce.250ms="searchUsers" placeholder="الاسم أو الرقم أو البريد">
                        <div class="ls-results" x-show="userOpen" x-cloak @click.outside="userOpen = false">
                            <template x-for="item in userResults" :key="item.id">
                                <button type="button" class="ls-result" @click="selectUser(item)"><strong x-text="item.label"></strong><small x-text="item.meta"></small></button>
                            </template>
                        </div>
                    </div>
                    <div class="ls-field" x-show="selectedUser.id"><span class="ls-badge is-active" x-text="selectedUser.label"></span></div>
                    <div class="ls-field">
                        <label class="ls-label">الصلاحيات</label>
                        <div class="ls-checks">
                            @foreach([
                                'can_upload' => 'رفع الملفات',
                                'can_create_sections' => 'إنشاء الأقسام',
                                'can_update_sections' => 'تعديل الأقسام',
                                'can_delete_sections' => 'حذف الأقسام',
                                'can_reorder_sections' => 'ترتيب الأقسام',
                                'can_manage_resources' => 'نقل وربط الملفات',
                            ] as $capability => $label)
                                <label class="ls-check"><input type="checkbox" name="capabilities[]" value="{{ $capability }}"> <span>{{ $label }}</span></label>
                            @endforeach
                        </div>
                    </div>
                    <div class="ls-field">
                        <label class="ls-label">نطاق الأقسام</label>
                        <div class="ls-scopes">
                            <label class="ls-scope"><span>بدون تحديد = المساحة كاملة</span></label>
                            @foreach($flatNodes as $node)
                                <label class="ls-scope"><input type="checkbox" name="node_ids[]" value="{{ $node->id }}"> <span>{{ $node->title }}</span></label>
                            @endforeach
                        </div>
                    </div>
                    <div class="ls-field"><label class="ls-label">تاريخ الانتهاء</label><input class="ls-input" type="datetime-local" name="expires_at"></div>
                    <button class="ls-btn is-primary w-100" type="submit" :disabled="!selectedUser.id"><i class="fas fa-user-check"></i> حفظ الصلاحيات</button>
                </form>
            </aside>
        </div>

        <section class="ls-section">
            <div class="ls-panel-head"><h2>المساهمون</h2><span class="ls-badge">{{ $contributors->count() }}</span></div>
            <div class="ls-table-wrap">
                <table class="ls-table">
                    <thead><tr><th>المستخدم</th><th>الصلاحيات</th><th>النطاق</th><th>الحالة</th><th></th></tr></thead>
                    <tbody>
                    @forelse($contributors as $contributor)
                        <tr>
                            <td><strong>{{ $contributor->user?->name ?? 'مستخدم محذوف' }}</strong><br><small class="text-muted">{{ $contributor->user?->email }}</small></td>
                            <td><div class="ls-badges">
                                @foreach(\App\Support\Library\LibraryContract::CAPABILITIES as $capability)
                                    @if($contributor->{$capability})<span class="ls-badge">{{ $capabilityLabels[$capability] }}</span>@endif
                                @endforeach
                            </div></td>
                            <td>{{ $contributor->scopedNodes->isEmpty() ? 'المساحة كاملة' : $contributor->scopedNodes->pluck('title')->implode('، ') }}</td>
                            <td><span class="ls-badge {{ $contributor->is_active ? 'is-active' : 'is-off' }}">{{ $contributor->is_active ? 'نشط' : 'مسحوب' }}</span></td>
                            <td>
                                @if($contributor->is_active)
                                    <form method="POST" action="{{ route('admin.library-structure.contributors.revoke', $contributor) }}" onsubmit="return confirm('سحب جميع صلاحيات هذا المستخدم؟')">
                                        @csrf @method('DELETE')
                                        <button class="ls-icon-btn is-danger" title="سحب الصلاحية"><i class="fas fa-user-slash"></i></button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">لا توجد صلاحيات ممنوحة لهذه المساحة.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="ls-section">
            <div class="ls-panel-head"><h2>آخر العمليات</h2><span class="ls-badge">{{ $activities->count() }}</span></div>
            <div class="ls-table-wrap">
                <table class="ls-table">
                    <thead><tr><th>العملية</th><th>المنفذ</th><th>العنصر</th><th>الوقت</th></tr></thead>
                    <tbody>
                    @forelse($activities as $activity)
                        <tr>
                            <td>{{ $activityLabels[$activity->action] ?? $activity->action }}</td>
                            <td>{{ $activity->actor?->name ?? 'النظام' }}</td>
                            <td>{{ $activity->node?->title ?? $activity->resource?->title ?? '-' }}</td>
                            <td>{{ $activity->created_at?->format('Y/m/d h:i A') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">لا توجد عمليات مسجلة.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endif
</main>
@endsection

@push('scripts')
<script>
    function libraryStructure() {
        return {
            panel: 'node',
            nodeMode: 'add',
            selectedNode: {},
            parentId: null,
            parentLabel: '',
            uploadNodeId: null,
            uploadNodeLabel: '',
            subjectQuery: @js($subject?->name ?? ''),
            subjectResults: [],
            subjectOpen: false,
            userQuery: '',
            userResults: [],
            userOpen: false,
            selectedUser: {},
            updateNodeUrl: @js(route('admin.library-structure.nodes.update', '__NODE__')),
            subjectLookupUrl: @js(route('admin.library-structure.lookup-subjects')),
            userLookupUrl: @js(route('admin.library-structure.lookup-users')),
            currentType: @js($type),
            subjectId: @js($subject?->id),
            nodeUrl(template, id) { return template.replace('__NODE__', id || ''); },
            selectNode(node) { this.selectedNode = node; this.nodeMode = 'edit'; this.panel = 'node'; },
            openAdd(parentId, label) { this.parentId = parentId; this.parentLabel = parentId ? label : ''; this.nodeMode = 'add'; this.panel = 'node'; },
            openUpload(id, label) { this.uploadNodeId = id; this.uploadNodeLabel = label; this.panel = 'upload'; },
            async searchSubjects() {
                if (this.subjectQuery.trim().length < 2) { this.subjectResults = []; this.subjectOpen = false; return; }
                const response = await fetch(`${this.subjectLookupUrl}?q=${encodeURIComponent(this.subjectQuery.trim())}`, { headers: { 'Accept': 'application/json' } });
                const data = await response.json(); this.subjectResults = data.results || []; this.subjectOpen = true;
            },
            chooseSubject(id) {
                const url = new URL(@js(route('admin.library-structure.index')), window.location.origin);
                url.searchParams.set('subject_id', id); url.searchParams.set('type', this.currentType); window.location.assign(url.toString());
            },
            async searchUsers() {
                if (!this.subjectId || this.userQuery.trim().length < 2) { this.userResults = []; this.userOpen = false; return; }
                const url = `${this.userLookupUrl}?subject_id=${this.subjectId}&q=${encodeURIComponent(this.userQuery.trim())}`;
                const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
                const data = await response.json(); this.userResults = data.results || []; this.userOpen = true;
            },
            selectUser(item) { this.selectedUser = item; this.userQuery = item.label; this.userOpen = false; },
        };
    }
</script>
@endpush
