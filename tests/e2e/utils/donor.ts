import {randomBytes} from 'crypto';
import {wp} from './wp-cli';

export type DonorAccount = {userId: number; donorId: number; username: string; password: string};

/**
 * Creates a WordPress user with a donor profile attached and returns what a spec needs to log in as it.
 *
 * The donor dashboard only treats a visitor as signed in when their WordPress user owns a donor, and
 * no REST route links the two, so this goes through the model over WP-CLI like `createDonation` does.
 */
export function createDonorAccount(): DonorAccount {
    const username = `donor${Date.now()}${randomBytes(2).toString('hex')}`;
    const password = randomBytes(12).toString('hex');

    const ids = wp(
        'eval',
        `$userId = wp_insert_user([
            'user_login' => '${username}', 'user_pass' => '${password}',
            'user_email' => '${username}@example.test', 'role' => 'give_donor',
        ]);
        if (is_wp_error($userId)) {
            WP_CLI::error($userId->get_error_message());
        }
        $donor = \\Give\\Donors\\Models\\Donor::create([
            'name' => 'Dana Donor', 'firstName' => 'Dana', 'lastName' => 'Donor',
            'email' => '${username}@example.test', 'userId' => $userId,
        ]);
        echo $userId . ',' . $donor->id;`
    );

    const [userId, donorId] = ids.split(',').map(Number);

    return {userId, donorId, username, password};
}

/**
 * Removes the user and donor `createDonorAccount` made, so reruns do not pile donors into the list tables.
 */
export function deleteDonorAccount(account: DonorAccount): void {
    wp(
        'eval',
        `require_once ABSPATH . 'wp-admin/includes/user.php';
        $donor = \\Give\\Donors\\Models\\Donor::find(${account.donorId});
        if ($donor) {
            $donor->delete();
        }
        wp_delete_user(${account.userId});`
    );
}
