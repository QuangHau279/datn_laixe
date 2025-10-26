@extends('layouts.app')

@section('title', 'Danh mục Biển báo Giao thông')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Danh mục Biển báo Giao thông Việt Nam</h1>
            
            <div class="row">
                @foreach($categories as $category)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">{{ $category->name }}</h5>
                            <p class="card-text">
                                <span class="badge bg-primary">{{ $category->signs->count() }} biển báo</span>
                            </p>
                            <p class="card-text">
                                Danh mục biển báo {{ strtolower($category->name) }} theo quy định của Việt Nam.
                            </p>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('traffic-signs.show', $category->slug) }}" class="btn btn-primary">
                                Xem chi tiết
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            @if($categories->isEmpty())
            <div class="alert alert-info">
                <h4>Chưa có dữ liệu biển báo</h4>
                <p>Hệ thống đang trong quá trình cập nhật dữ liệu. Vui lòng quay lại sau.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
