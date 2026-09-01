@php
    /** @var \App\Models\LibraryNode $node */
    $node = $item['node'];
    $children = $item['children'];
@endphp

<div class="ls-node-wrap" style="--node-depth: {{ $depth }}">
    <article class="ls-node {{ $node->is_active ? '' : 'is-inactive' }}">
        <button type="button"
                class="ls-node-main"
                @click="selectNode(@js([
                    'id' => $node->id,
                    'title' => $node->title,
                    'icon' => $node->icon,
                    'is_active' => $node->is_active,
                    'is_system' => $node->is_system,
                ]))">
            <span class="ls-node-icon"><i class="fas fa-folder" aria-hidden="true"></i></span>
            <span class="ls-node-copy">
                <strong>{{ $node->title }}</strong>
                <small>
                    {{ $node->is_system ? 'قسم أساسي' : 'قسم مخصص' }}
                    <span aria-hidden="true">·</span>
                    {{ $node->placements_count }} ارتباط
                </small>
            </span>
        </button>

        <div class="ls-node-actions">
            <button type="button" class="ls-icon-btn" title="إضافة قسم فرعي"
                    @click="openAdd({{ $node->id }}, @js($node->title))">
                <i class="fas fa-folder-plus" aria-hidden="true"></i>
            </button>
            <button type="button" class="ls-icon-btn" title="رفع ملف"
                    @click="openUpload({{ $node->id }}, @js($node->title))">
                <i class="fas fa-cloud-arrow-up" aria-hidden="true"></i>
            </button>
            <form action="{{ route('admin.library-structure.nodes.move', $node) }}" method="POST">
                @csrf
                <input type="hidden" name="direction" value="up">
                <button class="ls-icon-btn" title="تحريك لأعلى" type="submit">
                    <i class="fas fa-arrow-up" aria-hidden="true"></i>
                </button>
            </form>
            <form action="{{ route('admin.library-structure.nodes.move', $node) }}" method="POST">
                @csrf
                <input type="hidden" name="direction" value="down">
                <button class="ls-icon-btn" title="تحريك لأسفل" type="submit">
                    <i class="fas fa-arrow-down" aria-hidden="true"></i>
                </button>
            </form>
            <form action="{{ route('admin.library-structure.nodes.update', $node) }}" method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" name="toggle_active" value="1">
                <button class="ls-icon-btn" title="{{ $node->is_active ? 'تعطيل' : 'تفعيل' }}" type="submit">
                    <i class="fas {{ $node->is_active ? 'fa-eye-slash' : 'fa-eye' }}" aria-hidden="true"></i>
                </button>
            </form>
            @unless($node->is_system)
                <form action="{{ route('admin.library-structure.nodes.destroy', $node) }}" method="POST"
                      onsubmit="return confirm('سيختفي هذا القسم وفروعه من المكتبة. هل تريد المتابعة؟')">
                    @csrf
                    @method('DELETE')
                    <button class="ls-icon-btn is-danger" title="حذف القسم" type="submit">
                        <i class="fas fa-trash" aria-hidden="true"></i>
                    </button>
                </form>
            @endunless
        </div>
    </article>

    @if($children->isNotEmpty())
        <div class="ls-node-children">
            @foreach($children as $child)
                @include('admin.library._structure_node', ['item' => $child, 'depth' => $depth + 1])
            @endforeach
        </div>
    @endif
</div>
