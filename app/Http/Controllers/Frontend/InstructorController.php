namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class InstructorController extends Controller
{
    public static function getActiveInstructors()
    {
        // '3.png' অনুযায়ী status 1 মানে active
        return DB::table('instructors')
            ->where('status', 1)
            ->orderBy('id', 'desc')
            ->get();
    }
}