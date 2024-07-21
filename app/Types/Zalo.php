<?php

namespace App\Types;

/**
 * @psalm-type FriendType = array{
 *     userId: string,
 *     username: string,
 *     displayName: string,
 *     zaloName: string,
 *     avatar: string,
 *     bgavatar: string,
 *     cover: string,
 *     gender: int,
 *     dob: int,
 *     sdob: string,
 *     status: string,
 *     phoneNumber: string,
 *     isFr: int,
 *     isBlocked: int,
 *     lastActionTime: int,
 *     lastUpdateTime: int,
 *     isActive: int,
 *     key: int,
 *     type: int,
 *     isActivePC: int,
 *     isActiveWeb: int,
 *     isValid: int,
 *     userKey: string,
 *     accountStatus: int,
 *     oaInfo: null|null,
 *     user_mode: int,
 *     globalId: string,
 *     bizPkg: array{
 *         label: ?string,
 *         pkgId: int
 *     },
 *     createdTs: int
 * }
 */
class Zalo
{
}
