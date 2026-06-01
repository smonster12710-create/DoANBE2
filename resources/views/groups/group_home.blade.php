@extends('dashboard')

@section('content')
<div style="padding: 20px; max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
    
    {{-- CỘT TRÁI: DANH SÁCH NHÓM --}}
    <div>
        <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.1); margin-bottom: 20px;">
            <h2 style="font-size: 18px; margin-bottom: 15px; border-bottom: 2px solid #e4e6eb; padding-bottom: 10px;">Hội nhóm của bạn ({{ $myGroups->count() }})</h2>
            @if($myGroups->count() > 0)
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    @foreach($myGroups as $group)
                        <a href="{{ route('groups.show', $group->slug) }}" style="display: flex; align-items: center; gap: 12px; padding: 10px; border: 1px solid #e4e6eb; border-radius: 8px; text-decoration: none; color: inherit;">
                            {{-- ĐÃ SỬA: Tự động nhận diện Link mạng (http) hoặc Link local trong Storage --}}
                            <img src="{{ $group->cover ? (Str::startsWith($group->cover, ['http://', 'https://']) ? $group->cover : Storage::url($group->cover) . '?v=' . time()) : asset('img/user/user.jpg') }}" style="width: 50px; height: 50px; border-radius: 8px; object-fit: cover;">
                            <div>
                                <strong style="display: block; font-size: 15px;">{{ $group->name }}</strong>
                                <span style="font-size: 12px; color: #65676b;">Quyền: {{ ucfirst($group->pivot->role) }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <p style="color: #65676b; text-align: center;">Bạn chưa tham gia nhóm nào.</p>
            @endif
        </div>

        <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.1);">
            <h2 style="font-size: 18px; margin-bottom: 15px; border-bottom: 2px solid #e4e6eb; padding-bottom: 10px;">Khám phá nhóm mới</h2>
            @if($suggestedGroups->count() > 0)
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    @foreach($suggestedGroups as $group)
                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px; border: 1px solid #e4e6eb; border-radius: 8px;">
                            <a href="{{ route('groups.show', $group->slug) }}" style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: 12px;">
                                {{-- ĐÃ SỬA: Tự động nhận diện Link mạng (http) hoặc Link local trong Storage --}}
                                <img src="{{ $group->cover ? (Str::startsWith($group->cover, ['http://', 'https://']) ? $group->cover : Storage::url($group->cover) . '?v=' . time()) : asset('img/user/user.jpg') }}" style="width: 50px; height: 50px; border-radius: 8px; object-fit: cover;">
                                <div>
                                    <strong style="display: block;">{{ $group->name }}</strong>
                                    <span style="font-size: 12px; color: #65676b;">{{ ucfirst($group->privacy) }}</span>
                                </div>
                            </a>
                            <form action="{{ route('groups.join', $group->slug) }}" method="POST">
                                @csrf
                                <button type="submit" style="background: #e51f28; color: #fff; border: none; padding: 6px 12px; border-radius: 6px; font-weight: bold; cursor: pointer;">Tham gia</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @else
                <p style="color: #65676b; text-align: center;">Không có nhóm gợi ý nào mới.</p>
            @endif
        </div>
    </div>

    {{-- CỘT PHẢI: FORM TẠO NHÓM --}}
    <div>
        <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.1); position: sticky; top: 20px;">
            <h2 style="font-size: 18px; margin-bottom: 15px; border-bottom: 2px solid #e4e6eb; padding-bottom: 10px;">Tạo nhóm mới</h2>
            
            @if ($errors->any())
                <div style="background: #ffebe6; color: #e51f28; padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 13px;">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('groups.store') }}" method="POST">
                @csrf
                <div style="margin-bottom: 15px;">
                    <label style="display: block; font-weight: bold; margin-bottom: 5px;">Tên nhóm</label>
                    <input type="text" name="name" required placeholder="Nhập tên nhóm..." style="width: 100%; padding: 8px; border: 1px solid #e4e6eb; border-radius: 6px; box-sizing: border-box;">
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; font-weight: bold; margin-bottom: 5px;">Quyền riêng tư</label>
                    <select name="privacy" style="width: 100%; padding: 8px; border: 1px solid #e4e6eb; border-radius: 6px; box-sizing: border-box;">
                        <option value="public">Công khai</option>
                        <option value="private">Riêng tư</option>
                    </select>
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; font-weight: bold; margin-bottom: 5px;">Mô tả</label>
                    <textarea name="description" rows="3" placeholder="Mô tả ngắn..." style="width: 100%; padding: 8px; border: 1px solid #e4e6eb; border-radius: 6px; resize: none; box-sizing: border-box;"></textarea>
                </div>
                <button type="submit" style="width: 100%; background: #111; color: #fff; padding: 10px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">Tạo luôn</button>
            </form>
        </div>
    </div>
</div>
@endsection