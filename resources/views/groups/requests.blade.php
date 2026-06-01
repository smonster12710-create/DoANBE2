@extends('dashboard')

@section('content')
<div style="max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.1);">
        <div style="display: flex; justify-content: space-between; margin-bottom: 20px; border-bottom: 1px solid #e4e6eb; padding-bottom: 10px;">
            <h2 style="font-size: 18px; margin: 0;">Yêu cầu tham gia nhóm</h2>
            <a href="{{ route('groups.show', $group->slug) }}" style="color: #e51f28; text-decoration: none; font-weight: bold;">← Trở lại nhóm</a>
        </div>

        @if($pendingMembers->count() > 0)
            <div style="display: flex; flex-direction: column; gap: 12px;">
                @foreach($pendingMembers as $user)
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px; border: 1px solid #e4e6eb; border-radius: 8px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <img src="{{ asset('img/user/user.jpg') }}" style="width: 40px; height: 40px; border-radius: 50%;">
                            <strong>{{ $user->fullname }}</strong>
                        </div>
                        <form action="{{ route('groups.approve', ['slug' => $group->slug, 'userId' => $user->id]) }}" method="POST">
                            @csrf
                            <button type="submit" style="background: #e51f28; color: #fff; border: none; padding: 6px 12px; border-radius: 6px; font-weight: bold; cursor: pointer;">Phê duyệt</button>
                        </form>
                    </div>
                @endforeach
            </div>
        @else
            <p style="color: #65676b; text-align: center; padding: 10px;">Không có yêu cầu nào đang chờ phê duyệt.</p>
        @endif
    </div>
</div>
@endsection