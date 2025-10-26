@extends('layouts.app')

@section('title', 'Biển báo Giao thông')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Biển báo Giao thông Việt Nam</h1>
            
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Hệ thống Biển báo Giao thông</h5>
                            <p class="card-text">
                                Hệ thống biển báo giao thông Việt Nam được quy định theo QCVN 41:2019/BGTVT, 
                                bao gồm các nhóm biển báo chính: Cấm, Nguy hiểm, Hiệu lệnh, Chỉ dẫn và Biển phụ.
                            </p>
                            <p class="card-text">
                                Mỗi loại biển báo có ý nghĩa và quy định riêng, giúp người tham gia giao thông 
                                hiểu và tuân thủ đúng luật lệ an toàn giao thông.
                            </p>
                            <a href="{{ route('traffic-signs.index') }}" class="btn btn-primary">
                                Xem danh mục Biển báo
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title">Thống kê</h6>
                            <ul class="list-unstyled">
                                <li><strong>{{ \App\Models\TrafficSignCategory::count() }}</strong> Danh mục</li>
                                <li><strong>{{ \App\Models\TrafficSign::count() }}</strong> Biển báo</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="card mt-3">
                        <div class="card-body">
                            <h6 class="card-title">Liên kết hữu ích</h6>
                            <ul class="list-unstyled">
                                <li><a href="{{ route('traffic-signs.show', 'cam') }}">Biển báo Cấm</a></li>
                                <li><a href="{{ route('traffic-signs.show', 'nguy-hiem') }}">Biển báo Nguy hiểm</a></li>
                                <li><a href="{{ route('traffic-signs.show', 'hieu-lenh') }}">Biển báo Hiệu lệnh</a></li>
                                <li><a href="{{ route('traffic-signs.show', 'chi-dan') }}">Biển báo Chỉ dẫn</a></li>
                                <li><a href="{{ route('traffic-signs.show', 'phu') }}">Biển báo Phụ</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
