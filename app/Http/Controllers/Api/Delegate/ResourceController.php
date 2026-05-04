    /**
     * Search global library for resources.
     */
    public function searchLibrary(Request $request)
    {
        $query = $request->get('q');
        $user = $request->user();

        $resources = CourseResource::with(['subject:id,name,code', 'uploader:id,name'])
            ->whereHas('subject', function ($q) use ($user) {
                $q->where('major_id', $user->major_id);
            })
            ->when($query, function ($q) use ($query) {
                $q->where(function($sq) use ($query) {
                    $sq->where('title', 'like', "%{$query}%")
                      ->orWhereHas('subject', function ($ssq) use ($query) {
                          $ssq->where('name', 'like', "%{$query}%");
                      });
                });
            })
            ->when($request->filled('category') && $request->category != 'all', function ($q) use ($request) {
                $q->where('category', $request->category);
            })
            ->when($request->filled('subject_id'), function ($q) use ($request) {
                $q->where('subject_id', $request->subject_id);
            })
            ->latest()
            ->take(50)
            ->get();

        return $this->success($resources, 'تم البحث في المكتبة بنجاح');
    }
