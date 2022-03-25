<?php

namespace App\Entity;

use App\Entity\Market\RequestRole;
use InvalidArgumentException;
use DateTimeInterface;
use DateTime;
use DateTimeZone;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Knp\DoctrineBehaviors\Exception\ShouldNotHappenException;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;
use App\Repository\UserRepository;
use App\Validator as AcmeAssert;
use App\Entity\Market\Commodity;
use App\Entity\Market\CommodityProduct;
use App\Entity\Market\CommodityService;
use App\Entity\Market\CommodityKit;
use App\Entity\Market\Phone;
use App\Entity\Market\UserProperty;
use App\Entity\Market\UserFavorite;
use App\Entity\Market\Notification\Notification;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(fields={"email"}, message="There is already an account with this email")
 * @AcmeAssert\DateOfBirth()
 * @AcmeAssert\PasswordConstraint()
 */
class User implements UserInterface
{
    use TimestampableTrait;

    public const ROLE_USER = 'ROLE_USER';
    public const ROLE_ADMIN_EDUCATION = 'ROLE_ADMIN_EDUCATION';
    public const ROLE_ADMIN_MARKET = 'ROLE_ADMIN_MARKET';
    public const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';
    public const ROLE_WHOLESALE_BUYER = 'ROLE_WHOLESALE_BUYER';
    public const ROLE_SALESMAN = 'ROLE_SALESMAN';
    public const ROLE_SERVICE_PROVIDER = 'ROLE_SERVICE_PROVIDER';

    public const GENDER_MALE = 0;
    public const GENDER_FEMALE = 1;
    public const GENDER_NULL = null;

    private const SESSION_PHONE_VERIFICATION_CODE = 'code_verify_phone';

    public static  array $rolesInRequestRoles = [
        'wholesale-bayer' => self::ROLE_WHOLESALE_BUYER,
        'salesman' =>  self::ROLE_SALESMAN,
        'service-provider' => self::ROLE_SERVICE_PROVIDER,
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Assert\Email()
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isVerified = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $gender;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Assert\Type("\DateTimeInterface")
     */
    private $dateOfBirth;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $avatar;

    private $plainPassword = null;

    /**
     * @ORM\ManyToOne(targetEntity=Region::class)
     */
    private $region;

    /**
     * @ORM\ManyToOne(targetEntity=District::class)
     */
    private ?District $district;

    /**
     * @ORM\ManyToOne(targetEntity=Locality::class)
     */
    private ?Locality $locality;


    /**
     * @var DateTimeInterface
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     */
    protected $createdAt;

    /**
     * @var DateTimeInterface
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     */
    protected $updatedAt;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isNewsSub;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $verifyPhone;

    /**
     * @ORM\OneToMany(targetEntity=UserDownload::class, mappedBy="user")
     */
    private $userDownloads;

    /**
     * @ORM\OneToMany(targetEntity=ItemRegistration::class, mappedBy="userId")
     */
    private $itemRegistrations;

    /**
     * @ORM\ManyToOne(targetEntity=Activity::class, inversedBy="users")
     */
    private $activity;

    /**
     * @ORM\OneToMany(targetEntity=Media::class, mappedBy="user")
     */
    private $media;

    /**
     * @ORM\ManyToMany(targetEntity=Crop::class, inversedBy="users")
     */
    private $crops;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isBanned = false;

    /**
     * @ORM\Column(type="boolean",options={"default" : false}, nullable=true)
     */
    private bool $isOnline = false;

    /**
     * @ORM\OneToMany(targetEntity=Notification::class, mappedBy="sender")
     */
    private Collection $notificationsSent;

    /**
     * @ORM\OneToMany(targetEntity=Notification::class, mappedBy="receiver", orphanRemoval=true)
     */
    private Collection $notificationsReceived;

    /**
     * @ORM\OneToOne(targetEntity=UserProperty::class, inversedBy="user", cascade={"persist", "remove"})
     * @Assert\Valid
     */
    private $userProperty;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="authorizedUser")
     */
    private $comments;

    /**
     * @ORM\OneToMany(targetEntity=Market\Phone::class, mappedBy="user", orphanRemoval=true, cascade={"persist"})
     */
    private $phones;

    /**
     * @ORM\OneToMany(targetEntity=Market\Commodity::class, mappedBy="user")
     */
    private $commodities;

    /**
     * @ORM\OneToMany(targetEntity=Market\UserFavorite::class, mappedBy="userFavorite", orphanRemoval=true)
     */
    private Collection $favorites;

    /**
     * @ORM\OneToMany(targetEntity=RequestRole::class, mappedBy="user")
     */
    private $requestRoles;

    /**
     * @throws ShouldNotHappenException
     */
    public function __construct()
    {
        $this->updateTimestamps();
        $this->roles = [
            self::ROLE_USER,
        ];
        $this->userDownloads = new ArrayCollection();
        $this->media = new ArrayCollection();
        $this->crops = new ArrayCollection();
        $this->notificationsSent = new ArrayCollection();
        $this->notificationsReceived = new ArrayCollection();
        $this->itemRegistrations = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->phones = new ArrayCollection();
        $this->commodities = new ArrayCollection();
        $this->favorites = new ArrayCollection();
        $this->requestRoles = new ArrayCollection();
    }

    /**
     * Get user available roles values.
     *
     * @return  string[]                    Roles set.
     */
    public static function getAvailableRoles(): array
    {
        return [
            self::ROLE_USER,
            self::ROLE_ADMIN_EDUCATION,
            self::ROLE_ADMIN_MARKET,
            self::ROLE_SUPER_ADMIN,
            self::ROLE_WHOLESALE_BUYER,
            self::ROLE_SALESMAN,
            self::ROLE_SERVICE_PROVIDER,
        ];
    }

    /**
     * Get user available roles values.
     *
     * @return  string[]                    Roles set.
     */
    public static function getNameRoles(): array
    {
        return [
            self::ROLE_USER => 'Покупець',
            self::ROLE_ADMIN_EDUCATION => 'Адміністратор Агро-Вікі',
            self::ROLE_ADMIN_MARKET => 'Адміністратор торгового майданчика',
            self::ROLE_SUPER_ADMIN => 'Супер адміністратор',
            self::ROLE_WHOLESALE_BUYER => 'Оптовий покупець',
            self::ROLE_SALESMAN => 'Продавець',
            self::ROLE_SERVICE_PROVIDER => 'Постачальник послуг',
        ];
    }

    public static function getNormalRoleByCode(string $code): ?string
    {
        switch ($code) {
            case 'buyer':
                return self::ROLE_USER;
            case 'wholesale-bayer':
                return self::ROLE_WHOLESALE_BUYER;
            case 'salesman':
                return self::ROLE_SALESMAN;
            case 'service-provider':
                return self::ROLE_SERVICE_PROVIDER;
            default:
                return null;
        }
    }
    /**
     * @param $code
     * @return string|null
     */
    public static function getNameRolesByCode($code): ?string
    {
        $roleCodes = [
            'buyer' => self::ROLE_USER,
            'wholesale-bayer' => self::ROLE_WHOLESALE_BUYER,
            'salesman' => self::ROLE_SALESMAN,
            'service-provider' => self::ROLE_SERVICE_PROVIDER,
        ];

        $roles = self::getNameRoles();

        if (isset($roleCodes[$code])) {
            return $roles[$roleCodes[$code]];
        }
        return '';
    }

    /**
     * Get user available roles values.
     *
     * @return  string[]                    Roles set.
     */
    public function getCurrentNameRoles(): array
    {
        $roleName = [];
        foreach ($this->getRoles() as $role) {
            $roleName[] = self::getNameRoles()[$role];
        }
        return $roleName;
    }

    /**
     * @return string
     */
    public function getCurrentNameRolesString(): string
    {
        return implode(', ', $this->getCurrentNameRoles());
    }


    /**
     * Get verify code session index.
     *
     * @param string $phone Phone.
     *
     * @return  string                      Index.
     */
    public static function getPhoneVerifyCodeSessionIndex(string $phone): string
    {
        $prefix = self::SESSION_PHONE_VERIFICATION_CODE;

        return "$prefix:$phone";
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $password): void
    {
        $this->plainPassword = $password;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string)$this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        return array_unique(array_merge($this->roles, [
            self::ROLE_USER,
        ]));
    }

    public function setRoles(array $roles): self
    {
        foreach ($roles as $role) {
            if (!in_array($role, self::getAvailableRoles())) {
                throw new InvalidArgumentException("unknown role $roles");
            }
        }

        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string)$this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getGender()
    {
        return $this->gender;
    }

    public function setGender($gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getDateOfBirth(): ?DateTimeInterface
    {
        return $this->dateOfBirth;
    }

    public function setDateOfBirth(?DateTimeInterface $dateOfBirth): self
    {
        $this->dateOfBirth = $dateOfBirth;

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getRegion(): ?Region
    {
        return $this->region;
    }

    public function setRegion(?Region $region): self
    {
        $this->region = $region;

        return $this;
    }

    public function getDistrict(): ?District
    {
        return $this->district;
    }

    public function setDistrict(?District $district): self
    {
        $this->district = $district;

        return $this;
    }

    public function getLocality(): ?Locality
    {
        return $this->locality;
    }

    public function setLocality(?Locality $locality): self
    {
        $this->locality = $locality;

        return $this;
    }

    public function getAddress():string
    {
        return $this->getRegion() ?? '' . ' ' . $this->getDistrict() ?? '' . ' ' . $this->getLocality() ?? '';
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function setUpdatedAt(DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Updates createdAt and updatedAt timestamps.
     */
    public function updateTimestamps(): void
    {
        // Create a datetime with microseconds
        $dateTime = DateTime::createFromFormat('U.u', sprintf('%.6F', microtime(true)));

        if ($dateTime === false) {
            throw new ShouldNotHappenException();
        }

        $dateTime->setTimezone(new DateTimeZone(date_default_timezone_get()));

        if ($this->createdAt === null) {
            $this->createdAt = $dateTime;
            $this->updatedAt = $dateTime;
        }

        $this->updatedAt = $dateTime;
    }

    public function __toString(): string
    {
        return $this->getName() ?? $this->getEmail() ?? '';
    }

    /**
     * @return mixed
     */
    public function getIsNewsSub(): bool
    {
        return $this->isNewsSub ?? false;
    }

    /**
     * @param mixed $isNewsSub
     */
    public function setIsNewsSub($isNewsSub): void
    {
        $this->isNewsSub = $isNewsSub;
    }

    public function getVerifyPhone(): ?bool
    {
        return $this->verifyPhone;
    }

    public function setVerifyPhone(?bool $verifyPhone): self
    {
        $this->verifyPhone = $verifyPhone;

        return $this;
    }

    /**
     * @return Collection|UserDownload[]
     */
    public function getUserDownloads(): Collection
    {
        return $this->userDownloads;
    }

    public function addUserDownload(UserDownload $userDownload): self
    {
        if (!$this->userDownloads->contains($userDownload)) {
            $this->userDownloads[] = $userDownload;
            $userDownload->setUser($this);
        }

        return $this;
    }

    public function removeUserDownload(UserDownload $userDownload): self
    {
        if ($this->userDownloads->removeElement($userDownload)) {
            // set the owning side to null (unless already changed)
            if ($userDownload->getUser() === $this) {
                $userDownload->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ItemRegistration[]
     */
    public function getItemRegistrations(): Collection
    {
        return $this->itemRegistrations;
    }

    public function addItemRegistration(ItemRegistration $itemRegistration): self
    {
        if (!$this->itemRegistrations->contains($itemRegistration)) {
            $this->itemRegistrations[] = $itemRegistration;
            $itemRegistration->setUserId($this);
        }

        return $this;
    }

    public function removeItemRegistration(ItemRegistration $itemRegistration): self
    {
        if ($this->itemRegistrations->removeElement($itemRegistration)) {
            // set the owning side to null (unless already changed)
            if ($itemRegistration->getUserId() === $this) {
                $itemRegistration->setUserId(null);
            }
        }

        return $this;
    }


    public function getActivity(): ?Activity
    {
        return $this->activity;
    }

    public function setActivity(?Activity $activity): self
    {
        $this->activity = $activity;

        return $this;
    }

    /**
     * @return Collection|Media[]
     */
    public function getMedia(): Collection
    {
        return $this->media;
    }

    public function addMedia(Media $media): self
    {
        if (!$this->media->contains($media)) {
            $this->media[] = $media;
            $media->setUser($this);
        }

        return $this;
    }

    public function removeMedia(Media $media): self
    {
        if ($this->media->removeElement($media)) {
            // set the owning side to null (unless already changed)
            if ($media->getUser() === $this) {
                $media->setUser(null);
            }
        }

        return $this;
    }

    public function getSurname(): string
    {
        $name = explode(' ', $this->getName());
        if (count($name) === 1) {
            return $name[0];
        }

        count($name) === 3
            ? $surname = $name[1] . $name[2]
            : $surname = $name[1];

        return $surname;
    }

    /**
     * @return Collection|Crop[]
     */
    public function getCrops(): Collection
    {
        return $this->crops;
    }

    public function addCrop(Crop $crop): self
    {
        if (!$this->crops->contains($crop)) {
            $this->crops[] = $crop;
        }

        return $this;
    }

    public function removeCrop(Crop $crop): self
    {
        $this->crops->removeElement($crop);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsBanned()
    {
        return $this->isBanned;
    }

    /**
     * @param mixed $isBanned
     */
    public function setIsBanned($isBanned): void
    {
        $this->isBanned = $isBanned;
    }

    /**
     * Virtual field for EasyAdmin
     */
    public function getStatus()
    {

    }

    /**
     * @return Collection|Notification[]
     */
    public function getNotificationsSent(): Collection
    {
        return $this->notificationsSent;
    }

    public function addNotificationsSent(Notification $notificationsSent): self
    {
        if (!$this->notificationsSent->contains($notificationsSent)) {
            $this->notificationsSent[] = $notificationsSent;
            $notificationsSent->setSender($this);
        }

        return $this;
    }

    public function removeNotificationsSent(Notification $notificationsSent): self
    {
        if ($this->notificationsSent->removeElement($notificationsSent)) {
            // set the owning side to null (unless already changed)
            if ($notificationsSent->getSender() === $this) {
                $notificationsSent->setSender(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Notification[]
     */
    public function getNotificationsReceived(): Collection
    {
        return $this->notificationsReceived;
    }

    public function addNotificationsReceived(Notification $notificationsReceived): self
    {
        if (!$this->notificationsReceived->contains($notificationsReceived)) {
            $this->notificationsReceived[] = $notificationsReceived;
            $notificationsReceived->setReceiver($this);
        }

        return $this;
    }

    public function removeNotificationsReceived(Notification $notificationsReceived): self
    {
        if ($this->notificationsReceived->removeElement($notificationsReceived)) {
            // set the owning side to null (unless already changed)
            if ($notificationsReceived->getReceiver() === $this) {
                $notificationsReceived->setReceiver(null);
            }
        }

        return $this;
    }

    public function getUserProperty(): ?UserProperty
    {
        return $this->userProperty;
    }

    public function setUserProperty(?UserProperty $userProperty): self
    {
        $this->userProperty = $userProperty;

        return $this;
    }

    public function getCourseRegistration()
    {
        $coursesRegistration = [];
        /** @var ItemRegistration $itemRegistration */
        foreach ($this->getItemRegistrations() as $itemRegistration) {
            if ($itemRegistration->getItemId() instanceof Course) {
                $coursesRegistration[] = $itemRegistration;
            }
        }
        return $coursesRegistration;
    }

    public function getWebinarRegistration()
    {
        $coursesRegistration = [];
        /** @var ItemRegistration $itemRegistration */
        foreach ($this->getItemRegistrations() as $itemRegistration) {
            if ($itemRegistration->getItemId() instanceof Webinar) {
                $coursesRegistration[] = $itemRegistration;
            }
        }
        return $coursesRegistration;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setAuthorizedUser($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getAuthorizedUser() === $this) {
                $comment->setAuthorizedUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Phone[]
     */
    public function getPhones(): Collection
    {
        return $this->phones;
    }

    public function addPhone(Phone $phone): self
    {
        if (!$this->phones->contains($phone)) {
            $this->phones[] = $phone;
            $phone->setUser($this);
        }

        return $this;
    }

    public function removePhone(Phone $phone): self
    {
        if ($this->phones->removeElement($phone)) {
            // set the owning side to null (unless already changed)
            if ($phone->getUser() === $this) {
                $phone->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Phone|null
     */
    public function getMainPhone(): ?Phone
    {
        foreach ($this->getPhones() as $phone) {
            if ($phone->getIsMain()) {
                return $phone;
            }
        }

        return null;
    }

    /**
     * @return Collection|Commodity[]
     */
    public function getCommodities(): Collection
    {
        return $this->commodities;
    }

    public function getCommodityProducts()
    {
        $products = [];
        foreach ($this->getCommodities() as $item) {
            if ($item instanceof CommodityProduct) {
                $products[] = $item;
            }
        }
        return $products;
    }

    public function getCommodityServices()
    {
        $services = [];
        foreach ($this->getCommodities() as $item) {
            if ($item instanceof CommodityService) {
                $services[] = $item;
            }
        }
        return $services;
    }

    public function getCommodityKits()
    {
        $kits = [];
        foreach ($this->getCommodities() as $item) {
            if ($item instanceof CommodityKit) {
                $kits[] = $item;
            }
        }
        return $kits;
    }

    public function addCommodity(Commodity $commodity): self
    {
        if (!$this->commodities->contains($commodity)) {
            $this->commodities[] = $commodity;
            $commodity->setUser($this);
        }

        return $this;
    }

    public function removeCommodity(Commodity $commodity): self
    {
        if ($this->commodities->removeElement($commodity)) {
            // set the owning side to null (unless already changed)
            if ($commodity->getUser() === $this) {
                $commodity->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|UserFavorite[]
     */
    public function getFavorites(): Collection
    {
        return $this->favorites;
    }

    public function addFavorite(UserFavorite $favorite): self
    {
        if (!$this->favorites->contains($favorite)) {
            $this->favorites[] = $favorite;
            $favorite->setCommodity($this);
        }

        return $this;
    }

    public function removeFavorite(UserFavorite $favorite): self
    {
        if ($this->favorites->removeElement($favorite)) {
            if ($favorite->getCommodity() === $this) {
                $favorite->setCommodity(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|RequestRole[]
     */
    public function getRequestRoles(): Collection
    {
        return $this->requestRoles;
    }

    public function addRequestRole(RequestRole $requestRole): self
    {
        if (!$this->requestRoles->contains($requestRole)) {
            $this->requestRoles[] = $requestRole;
            $requestRole->setUser($this);
        }

        return $this;
    }

    public function removeRequestRole(RequestRole $requestRole): self
    {
        if ($this->requestRoles->removeElement($requestRole)) {
            // set the owning side to null (unless already changed)
            if ($requestRole->getUser() === $this) {
                $requestRole->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @param string $roleCode
     * @return bool
     */
    public function getRequestRole(string $roleCode): bool
    {

        $roles = $this->getRequestRoles();
        /** @var RequestRole $role */
        foreach ($roles as $role) {
            if ($role->getRole() == $roleCode && $role->isActive() === true) {
                return true;
            }
        }
        return false;
    }

    public function getIsOnline(): bool
    {
        return $this->isOnline;
    }

    public function setIsOnline(bool $isOnline): void
    {
        $this->isOnline = $isOnline;
    }
}
